<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis;

use Closure;
use InvalidArgumentException;

/**
* @template T
*/
class DefinitionAssistant
{
    const ARG_INDEX_CLOSURE_GETTER = 2;

    const ARG_INDEX_CLOSURE_SETTER = 3;

    const IN_ARRAY_STRICT_MODE = true;

    const COUNT_EXPECTED_REQUIRED_PARAMETERS = 1;

    const PARAM_INDEX_FIRST = 0;

    const BOOL_IS_PARAM = true;

    const BOOL_IS_RETURN = false;

    /**
    * @var array<string, array<int, string>>
    *
    * @psalm-var array<class-string<T>, array<int, string>>
    */
    protected static $properties = [];

    /**
    * @var array<string, Closure>
    *
    * @psalm-var array<class-string<T>, Closure(string):?string>
    */
    protected static $getters = [];

    /**
    * @var array<string, Closure>
    *
    * @psalm-var array<class-string<T>, Closure(string):?string>
    */
    protected static $setters = [];

    /**
    * @psalm-param class-string<T> $type
    */
    public static function IsTypeUnregistered(string $type) : bool
    {
        return ! isset(static::$properties[$type]);
    }

    /**
    * @psalm-param class-string<T> $type
    * @psalm-param null|Closure(string):?string $getter
    * @psalm-param null|Closure(string):?string $setter
    */
    public static function RegisterType(
        string $type,
        ? Closure $getter,
        ? Closure $setter,
        string $property,
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
        }

        array_unshift($properties, $property);

        static::MaybeRegisterTypeGetter($type, $getter);
        static::MaybeRegisterTypeSetter($type, $setter);

        static::$properties[$type] = $properties;
    }

    /**
    * @psalm-param class-string<T> $type
    */
    public static function GetterMethodName(string $type, string $property) : ? string
    {
        if (
            in_array($property, static::$properties[$type] ?? [], self::IN_ARRAY_STRICT_MODE) &&
            isset(static::$getters[$type])
        ) {
            return static::$getters[$type]($property);
        }

        return self::CheckOtherTypes(self::$getters, $type, $property);
    }

    /**
    * @psalm-param class-string<T> $type
    */
    public static function SetterMethodName(string $type, string $property) : ? string
    {
        if (
            in_array($property, static::$properties[$type] ?? [], self::IN_ARRAY_STRICT_MODE) &&
            isset(static::$setters[$type])
        ) {
            return static::$setters[$type]($property);
        }

        return self::CheckOtherTypes(self::$setters, $type, $property);
    }

    /**
    * @param string|object $maybe
    *
    * @psalm-param class-string<T>|T $maybe
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
    * @psalm-param array<class-string<T>, Closure(string):?string> $otherTypes
    * @psalm-param class-string<T> $type
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
                return $getter($property);
            }
        }

        return null;
    }

    /**
    * @psalm-param class-string<T> $type
    * @psalm-param null|Closure(string):?string $getter
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

            self::$getters[$type] = $getter;
        }
    }

    /**
    * @psalm-param class-string<T> $type
    * @psalm-param null|Closure(string):?string $setter
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

            self::$setters[$type] = $setter;
        }
    }
}
