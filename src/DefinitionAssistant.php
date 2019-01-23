<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis;

use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionType;

class DefinitionAssistant
{
    const ARG_INDEX_CLOSURE_GETTER = 2;

    const ARG_INDEX_CLOSURE_SETTER = 3;

    const IN_ARRAY_STRICT_MODE = true;

    const COUNT_EXPECT_AT_LEAST_ONE_PROPERTY = 1;

    const COUNT_EXPECTED_REQUIRED_PARAMETERS = 1;

    const PARAM_INDEX_FIRST = 0;

    const BOOL_IS_PARAM = true;

    const BOOL_IS_RETURN = false;

    /**
    * @var array<string, array<int, string>>
    */
    protected static $properties = [];

    /**
    * @var array<string, Closure>
    */
    protected static $getters = [];

    /**
    * @var array<string, Closure>
    */
    protected static $setters = [];

    public static function IsTypeUnregistered(string $type) : bool
    {
        if ( ! interface_exists($type) && ! class_exists($type)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                '() must be a class or interface!'
            );
        }

        return ! isset(static::$properties[$type]);
    }

    public static function RegisterType(
        string $type,
        ? Closure $getter,
        ? Closure $setter,
        string ...$properties
    ) : void {
        if ( ! static::IsTypeUnregistered($type)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                '() has already been registered!'
            );
        } elseif (is_null($getter) && is_null($setter)) {
            throw new InvalidArgumentException(
                'One or both of arguments 2 and 3 must be specified!'
            );
        } elseif (count($properties) < self::COUNT_EXPECT_AT_LEAST_ONE_PROPERTY) {
            throw new InvalidArgumentException(
                'Argument 4 must be specified!'
            );
        }

        static::MaybeRegisterTypeGetter($type, $getter);
        static::MaybeRegisterTypeSetter($type, $setter);

        static::$properties[$type] = $properties;
    }

    public static function GetterMethodName(string $type, string $property) : ? string
    {
        if (
            in_array($property, static::$properties[$type] ?? [], self::IN_ARRAY_STRICT_MODE) &&
            isset(static::$getters[$type])
        ) {
            /**
            * @var string|null
            */
            $out = static::$getters[$type]($property);

            return $out;
        }

        return null;
    }

    public static function SetterMethodName(string $type, string $property) : ? string
    {
        if (
            in_array($property, static::$properties[$type] ?? [], self::IN_ARRAY_STRICT_MODE) &&
            isset(static::$setters[$type])
        ) {
            /**
            * @var string|null
            */
            $out = static::$setters[$type]($property);

            return $out;
        }

        return null;
    }

    /**
    * @param mixed $maybe
    *
    * @return array<int, string>
    */
    public static function ObtainExpectedProperties($maybe) : array
    {
        if ( ! is_string($maybe) && ! is_object($maybe)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                '() must be either a string or an object, ' .
                gettype($maybe) .
                ' given!'
            );
        }

        /**
        * @var array<int, string>
        */
        $out = array_values(array_unique(array_reduce(
            array_filter(
                static::$properties,
                function (string $type) use ($maybe) : bool {
                    return is_a($maybe, $type, is_string($maybe));
                },
                ARRAY_FILTER_USE_KEY
            ),
            'array_merge',
            []
        )));

        return $out;
    }

    protected static function ValidateClosure(
        Closure $closure,
        int $argument,
        string $method
    ) : Closure {
        $ref = new ReflectionFunction($closure);

        if (self::COUNT_EXPECTED_REQUIRED_PARAMETERS !== $ref->getNumberOfRequiredParameters()) {
            throw new InvalidArgumentException(
                'Argument ' .
                $argument .
                ' passed to ' .
                $method .
                '() must be a closure with 1 required parameter!'
            );
        }

        $ref_param = $ref->getParameters()[self::PARAM_INDEX_FIRST];

        if ( ! $ref_param->hasType()) {
            throw new InvalidArgumentException(
                'Argument ' .
                $argument .
                ' passed to ' .
                $method .
                '() must be a closure with a typed first argument!'
            );
        }

        $closure = static::ValidateTypeExpectNonNullableString(
            $closure,
            $ref_param->getType(),
            $argument,
            $method,
            self::BOOL_IS_PARAM
        );

        if ( ! $ref->hasReturnType()) {
            throw new InvalidArgumentException(
                'Argument ' .
                $argument .
                ' passed to ' .
                $method .
                '() must have a return type!'
            );
        }

        $ref_return = $ref->getReturnType();

        return static::ValidateTypeExpectNonNullableString(
            $closure,
            $ref_return,
            $argument,
            $method,
            self::BOOL_IS_RETURN
        );
    }

    protected static function ValidateTypeExpectNonNullableString(
        Closure $closure,
        ? ReflectionType $ref,
        int $argument,
        string $method,
        bool $isParam
    ) : Closure {
        if ( ! ($ref instanceof ReflectionNamedType)) {
            throw new InvalidArgumentException(
                'Argument ' .
                $argument .
                ' passed to ' .
                $method .
                '() must be a closure with a' .
                ($isParam ? ' strongly-typed first argument' : ' named return type') .
                '!'
            );
        } elseif ($isParam ? $ref->allowsNull() : ( ! $ref->allowsNull())) {
            throw new InvalidArgumentException(
                'Argument ' .
                $argument .
                ' passed to ' .
                $method .
                '() must be a closure with a ' .
                ($isParam ? 'non-' : '') .
                'nullable ' .
                ($isParam ? 'first argument!' : 'return type!')
            );
        } elseif ('string' !== $ref->getName()) {
            throw new InvalidArgumentException(
                'Argument ' .
                $argument .
                ' passed to ' .
                $method .
                '() must be a closure with a string ' .
                ($isParam ? 'first argument' : 'return type') .
                ', ' .
                $ref->getName() .
                ' given!'
            );
        }

        return $closure;
    }

    private static function MaybeRegisterTypeGetter(string $type, ? Closure $getter) : void
    {
        if ( ! is_null($getter)) {
            if ( ! method_exists($type, '__get')) {
                throw new InvalidArgumentException(
                    'Argument 1 passed to ' .
                    __CLASS__ .
                    '::RegisterType() must declare __get() !'
                );
            }

            /**
            * @var string
            */
            $type = $type;

            /**
            * @var Closure
            */
            $getter = static::ValidateClosure(
                $getter,
                self::ARG_INDEX_CLOSURE_GETTER,
                (__CLASS__ . '::RegisterType')
            );

            self::$getters[$type] = $getter;
        }
    }

    private static function MaybeRegisterTypeSetter(string $type, ? Closure $setter) : void
    {
        if ( ! is_null($setter)) {
            if ( ! method_exists($type, '__set')) {
                throw new InvalidArgumentException(
                    'Argument 1 passed to ' .
                    __CLASS__ .
                    '::RegisterType() must declare __set() !'
                );
            }

            /**
            * @var string
            */
            $type = $type;

            /**
            * @var Closure
            */
            $setter = static::ValidateClosure(
                $setter,
                self::ARG_INDEX_CLOSURE_SETTER,
                (__CLASS__ . '::RegisterType')
            );

            self::$setters[$type] = $setter;
        }
    }
}
