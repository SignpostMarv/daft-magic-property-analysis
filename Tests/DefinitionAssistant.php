<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests;

use Closure;
use ReflectionType;
use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant as Base;

class DefinitionAssistant extends Base
{
    public static function ClearTypes() : void
    {
        static::$properties = [];
        static::$getters = [];
        static::$setters = [];
    }

    public static function PublicValidateClosure(
        Closure $closure,
        int $argument,
        string $method
    ) : Closure {
        return static::ValidateClosure($closure, $argument, $method);
    }

    public static function PublicValidateTypeExpectNonNullableString(
        Closure $closure,
        ? ReflectionType $ref,
        int $argument,
        string $method,
        bool $isParam
    ) : Closure {
        return static::ValidateTypeExpectNonNullableString(
            $closure,
            $ref,
            $argument,
            $method,
            $isParam
        );
    }

    public static function PublicCheckOtherTypesGetters(
        string $type,
        string $property
    ) : ? string {
        return static::CheckOtherTypes(self::$getters, $type, $property);
    }

    public static function PublicCheckOtherTypesSetters(
        string $type,
        string $property
    ) : ? string {
        return static::CheckOtherTypes(self::$setters, $type, $property);
    }
}
