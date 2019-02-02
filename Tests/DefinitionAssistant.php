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

    /**
    * @psalm-param class-string $type
    */
    public static function PublicCheckOtherTypesGetters(
        string $type,
        string $property
    ) : ? string {
        return static::CheckOtherTypes(self::$getters, $type, $property);
    }

    /**
    * @psalm-param class-string $type
    */
    public static function PublicCheckOtherTypesSetters(
        string $type,
        string $property
    ) : ? string {
        return static::CheckOtherTypes(self::$setters, $type, $property);
    }
}
