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
    *
    * @psalm-var array<class-string, array<int, string>>
    */
    protected static $properties = [];

    /**
    * @var array<string, Closure>
    *
    * @psalm-var array<class-string, Closure(string):?string>
    */
    protected static $getters = [];

    /**
    * @var array<string, Closure>
    *
    * @psalm-var array<class-string, Closure(string):?string>
    */
    protected static $setters = [];

    /**
    * @psalm-param class-string $type
    */
    public static function IsTypeUnregistered(string $type) : bool
    {
        return ! isset(static::$properties[$type]);
    }

    /**
    * @psalm-param class-string $type
    * @psalm-param null|Closure(string):?string $getter
    * @psalm-param null|Closure(string):?string $setter
    */
    public static function RegisterType(
        string $type,
        ? Closure $getter,
        ? Closure $setter,
        string ...$properties
    ) : void {
        if ( ! self::IsTypeUnregistered($type)) {
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

    /**
    * @psalm-param class-string $type
    */
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

        return self::CheckOtherTypes(self::$getters, $type, $property);
    }

    /**
    * @psalm-param class-string $type
    */
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

        return self::CheckOtherTypes(self::$setters, $type, $property);
    }

    /**
    * @param mixed $maybe
    *
    * @psalm-param class-string|object $maybe
    *
    * @return array<int, string>
    */
    public static function ObtainExpectedProperties($maybe) : array
    {
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

    /**
    * @param array<string, Closure> $otherTypes
    *
    * @psalm-param array<class-string, Closure> $otherTypes
    * @psalm-param class-string $type
    */
    protected static function CheckOtherTypes(
        array $otherTypes,
        string $type,
        string $property
    ) : ? string {
        foreach ($otherTypes as $otherType => $getter) {
            if (
                $otherType !== $type &&
                isset(self::$properties[$otherType]) &&
                in_array($property, self::$properties[$otherType], self::IN_ARRAY_STRICT_MODE)
            ) {
                /**
                * @var string|null
                */
                $out = $getter($property);

                return $out;
            }
        }

        return null;
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
        $not_named_type = ' named return type';

        if ($isParam) {
            $not_named_type = ' strongly-typed first argument';
        }

        if ( ! ($ref instanceof ReflectionNamedType)) {
            throw new InvalidArgumentException(
                'Argument ' .
                $argument .
                ' passed to ' .
                $method .
                '() must be a closure with a' .
                $not_named_type .
                '!'
            );
        }

        return static::ValidateTypeExpectNonNullableStringWithNamedType(
            $closure,
            $ref,
            $argument,
            $method,
            $isParam
        );
    }

    protected static function ValidateTypeExpectNonNullableStringWithNamedType(
        Closure $closure,
        ReflectionNamedType $ref,
        int $argument,
        string $method,
        bool $isParam
    ) : Closure {
        $nullable = '';
        $return_type = 'return type';

        if ($isParam) {
            $nullable = 'non-';
            $return_type = 'first argument';
        }

        if ($isParam ? $ref->allowsNull() : ( ! $ref->allowsNull())) {
            throw new InvalidArgumentException(
                'Argument ' .
                $argument .
                ' passed to ' .
                $method .
                '() must be a closure with a ' .
                $nullable .
                'nullable ' .
                $return_type .
                '!'
            );
        } elseif ('string' !== $ref->getName()) {
            throw new InvalidArgumentException(
                'Argument ' .
                $argument .
                ' passed to ' .
                $method .
                '() must be a closure with a string ' .
                $return_type .
                ', ' .
                $ref->getName() .
                ' given!'
            );
        }

        return $closure;
    }

    /**
    * @psalm-param class-string $type
    */
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

    /**
    * @psalm-param class-string $type
    */
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
