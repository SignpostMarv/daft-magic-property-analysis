<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\PHPStan;

use BadMethodCallException;
use InvalidArgumentException;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;
use ReflectionMethod;
use ReflectionParameter;
use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant;

class PropertyReflectionExtension implements PropertyReflection
{
    const PARAM_INDEX_FIRST = 0;

    const BOOL_NOT_STATIC = false;

    const BOOL_IS_NOT_FILE = false;

    const BOOL_IS_READABLE = true;

    const BOOL_IS_WRITABLE = true;

    const BOOL_IS_VARIADIC = false;

    /**
    * @var Type
    */
    protected $type;

    /**
    * @var Broker
    */
    protected $broker;

    /**
    * @var bool
    */
    protected $readable = false;

    /**
    * @var bool
    */
    protected $writable = false;

    /**
    * @var bool
    */
    protected $public;

    /**
    * @var ClassReflection|null
    */
    protected $readableReflection;

    /**
    * @var ClassReflection|null
    */
    protected $writableReflection;

    public function __construct(ClassReflection $classReflection, Broker $broker, string $property)
    {
        $this->broker = $broker;

        /**
        * @psalm-var class-string
        */
        $className = $classReflection->getName();

        $this->public = static::PropertyIsPublic($className, $property);

        $this->type = new MixedType();

        $this->SetupReflections($classReflection, $property);
    }

    public function getType() : Type
    {
        return $this->type;
    }

    public function isReadable() : bool
    {
        return $this->readable;
    }

    public function isWritable() : bool
    {
        return $this->writable;
    }

    public function isPublic() : bool
    {
        return $this->public;
    }

    public function isPrivate() : bool
    {
        return ! $this->isPublic();
    }

    public function isStatic() : bool
    {
        return self::BOOL_NOT_STATIC;
    }

    public function getDeclaringClass() : ClassReflection
    {
        $reflection = $this->readable ? $this->readableReflection : $this->writableReflection;

        if ( ! ($reflection instanceof ClassReflection)) {
            throw new BadMethodCallException(
                static::class .
                '::SetupReflections() was not called before ' .
                __METHOD__ .
                ' was called!'
            );
        }

        return $reflection;
    }

    protected static function DetermineDeclaringClass(
        Broker $broker,
        ReflectionMethod $refMethod
    ) : ClassReflection {
        $reflectionClass = $refMethod->getDeclaringClass();

        $filename = null;

        if (self::BOOL_IS_NOT_FILE !== $reflectionClass->getFileName()) {
            $filename = $reflectionClass->getFileName();
        }

        return $broker->getClassFromReflection(
            $reflectionClass,
            $reflectionClass->getName(),
            $reflectionClass->isAnonymous() ? $filename : null
        );
    }

    /**
    * @psalm-param class-string $className
    */
    protected static function PropertyIsPublic(string $className, string $property) : bool
    {
        $getter = DefinitionAssistant::GetterMethodName($className, $property);
        $setter = DefinitionAssistant::SetterMethodName($className, $property);

        return
            (
                is_string($getter) &&
                method_exists($className, $getter) &&
                (new ReflectionMethod($className, $getter))->isPublic()
            ) ||
            (
                is_string($setter) &&
                method_exists($className, $setter) &&
                (new ReflectionMethod($className, $setter))->isPublic()
            );
    }

    protected function SetupReflections(ClassReflection $classReflection, string $property) : void
    {
        /**
        * @psalm-var class-string
        */
        $className = $classReflection->getName();
        $getter = DefinitionAssistant::GetterMethodName($className, $property);
        $setter = DefinitionAssistant::SetterMethodName($className, $property);

        $this->writableReflection = $this->readableReflection = $classReflection;

        if (is_string($getter) && $classReflection->getNativeReflection()->hasMethod($getter)) {
            $this->readableReflection = $this->SetGetterProps(new ReflectionMethod(
                $className,
                $getter
            ));
        }

        if (is_string($setter) && $classReflection->getNativeReflection()->hasMethod($setter)) {
            $this->writableReflection = $this->SetSetterProps(new ReflectionMethod(
                $className,
                $setter
            ));
        }
    }

    protected function SetGetterProps(ReflectionMethod $refMethod) : ClassReflection
    {
        $this->readable = self::BOOL_IS_READABLE;

        if ($refMethod->isStatic()) {
            throw new InvalidArgumentException(
                'Implementations must not contain static getters!'
            );
        }

        if ($refMethod->hasReturnType()) {
            $this->type = TypehintHelper::decideTypeFromReflection($refMethod->getReturnType());
        }

        return static::DetermineDeclaringClass($this->broker, $refMethod);
    }

    protected function SetSetterProps(ReflectionMethod $refMethod) : ClassReflection
    {
        $this->writable = self::BOOL_IS_READABLE;

        if ($refMethod->isStatic()) {
            throw new InvalidArgumentException(
                'Implementations must not contain static setters!'
            );
        }

        $refParam = $refMethod->getParameters()[self::PARAM_INDEX_FIRST] ?? null;

        if (($refParam instanceof ReflectionParameter) && $refParam->hasType()) {
            $this->type = TypehintHelper::decideTypeFromReflection(
                $refParam->getType(),
                null,
                $refMethod->getDeclaringClass()->getName(),
                self::BOOL_IS_VARIADIC
            );
        }

        return static::DetermineDeclaringClass($this->broker, $refMethod);
    }
}
