<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\PHPStan;

use BadMethodCallException;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant;

class ClassReflectionExtension implements BrokerAwareExtension, PropertiesClassReflectionExtension
{
    const IN_ARRAY_STRICT_MODE = true;

    /**
    * @var Broker|null
    */
    private $broker;

    public function setBroker(Broker $broker) : void
    {
        $this->broker = $broker;
    }

    public function hasProperty(ClassReflection $classReflection, string $propertyName) : bool
    {
        $maybeExitEarly = $this->MaybeRegisterTypesOrExitEarly($classReflection, $propertyName);

        if (is_bool($maybeExitEarly)) {
            return $maybeExitEarly;
        }

        $className = $classReflection->getName();

        $getter = DefinitionAssistant::GetterMethodName($className, $propertyName);
        $setter = DefinitionAssistant::SetterMethodName($className, $propertyName);

        return
            in_array(
                $propertyName,
                DefinitionAssistant::ObtainExpectedProperties($className),
                self::IN_ARRAY_STRICT_MODE
            ) &&
            (
                (
                    is_string($getter) &&
                    $classReflection->getNativeReflection()->hasMethod($getter)
                ) ||
                (
                    is_string($setter) &&
                    $classReflection->getNativeReflection()->hasMethod($setter)
                )
            );
    }

    public function getProperty(ClassReflection $ref, string $propertyName) : PropertyReflection
    {
        if ( ! ($this->broker instanceof Broker)) {
            throw new BadMethodCallException(
                'Broker expected to be specified when calling ' .
                __METHOD__ .
                '()'
            );
        }

        return new PropertyReflectionExtension($ref, $this->broker, $propertyName);
    }

    protected function MaybeRegisterTypesOrExitEarly(
        ClassReflection $classReflection,
        string $propertyName
    ) : ? bool {
        return null;
    }
}
