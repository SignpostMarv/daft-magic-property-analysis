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

        /**
        * @psalm-var class-string
        */
        $className = $classReflection->getName();

        $expectedProperties = DefinitionAssistant::ObtainExpectedProperties($className);

        $getter = DefinitionAssistant::GetterMethodName($className, $propertyName);
        $setter = DefinitionAssistant::SetterMethodName($className, $propertyName);

        return
            in_array(
                $propertyName,
                $expectedProperties,
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

        return $this->ObtainPropertyReflection($ref, $this->broker, $propertyName);
    }

    protected function ObtainPropertyReflection(
        ClassReflection $ref,
        Broker $broker,
        string $propertyName
    ) : PropertyReflection {
        return new PropertyReflectionExtension($ref, $broker, $propertyName);
    }

    protected function MaybeRegisterTypesOrExitEarly(
        ClassReflection $classReflection,
        string $propertyName
    ) : ? bool {
        return null;
    }
}
