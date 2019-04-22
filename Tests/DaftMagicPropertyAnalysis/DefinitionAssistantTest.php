<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests\DaftMagicPropertyAnalysis;

use ArgumentCountError;
use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase as Base;
use ReflectionType;
use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant as BaseDefinitionAssistant;
use SignpostMarv\DaftMagicPropertyAnalysis\Tests\DefinitionAssistant;

class DefinitionAssistantTest extends Base
{
    /**
    * @return array<int, array{0:class-string, 1:Closure(string):?string, 2:Closure(string):?string, 3:array<int, string>, 4:array<string, string>, 5:array<string, string>}>
    */
    public function DataProviderRegisterTypeSuccessFromArray() : array
    {
        return [
            [
                Fixtures\ucwordsPrefixedImplementation::class,
                function (string $property) : ? string {
                    return
                        'foobar' === mb_strtolower($property)
                            ? ('Get' . ucwords($property))
                            : null;
                },
                function (string $property) : ? string {
                    return
                        'foobar' === mb_strtolower($property)
                            ? ('Set' . ucwords($property))
                            : null;
                },
                ['fooBar'],
                [
                    'fooBar' => 'GetFooBar',
                ],
                [
                    'fooBar' => 'SetFooBar',
                ],
            ],
        ];
    }

    /**
    * @param array<int, string> $properties
    * @param array<string, string> $getter_map
    * @param array<string, string> $setter_map
    *
    * @psalm-param class-string $type
    * @psalm-param null|Closure(string):?string $getter
    * @psalm-param null|Closure(string):?string $setter
    *
    * @dataProvider DataProviderRegisterTypeSuccessFromArray
    */
    public function testRegisterTypeSuccess(
        string $type,
        ? Closure $getter,
        ? Closure $setter,
        array $properties,
        array $getter_map,
        array $setter_map
    ) : void {
        DefinitionAssistant::ClearTypes();

        static::assertTrue(DefinitionAssistant::IsTypeUnregistered($type));

        DefinitionAssistant::RegisterType($type, $getter, $setter, ...$properties);

        static::assertFalse(DefinitionAssistant::IsTypeUnregistered($type));

        foreach ($properties as $property) {
            static::assertSame(
                $getter_map[$property] ?? null,
                DefinitionAssistant::GetterMethodName($type, $property)
            );
            static::assertSame(
                $setter_map[$property] ?? null,
                DefinitionAssistant::SetterMethodName($type, $property)
            );
        }

        $expected = DefinitionAssistant::ObtainExpectedProperties($type);

        foreach ($properties as $property) {
            static::assertTrue(in_array($property, $expected, true));

            $ytreporp = strrev($property);

            if ( ! in_array($ytreporp, $properties, true)) {
                static::assertNull(DefinitionAssistant::GetterMethodName($type, $ytreporp));
                static::assertNull(DefinitionAssistant::SetterMethodName($type, $ytreporp));
            }
        }

        DefinitionAssistant::ClearTypes();
    }

    /**
    * @param array<int, string> $properties
    *
    * @psalm-param class-string $type
    * @psalm-param null|Closure(string):?string $getter
    * @psalm-param null|Closure(string):?string $setter
    *
    * @dataProvider DataProviderRegisterTypeSuccessFromArray
    *
    * @depends testRegisterTypeSuccess
    */
    public function testRegisterTypeAlreadyRegistered(
        string $type,
        ? Closure $getter,
        ? Closure $setter,
        array $properties
    ) : void {
        DefinitionAssistant::ClearTypes();

        DefinitionAssistant::RegisterType($type, $getter, $setter, ...$properties);

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            BaseDefinitionAssistant::class .
            '::RegisterType() has already been registered!'
        );

        DefinitionAssistant::RegisterType($type, $getter, $setter, ...$properties);
    }

    public function testRegisterTypeMustImplementMagicGetter() : void
    {
        static::assertTrue(DefinitionAssistant::IsTypeUnregistered(Closure::class));

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            BaseDefinitionAssistant::class .
            '::RegisterType() must declare __get() !'
        );

        DefinitionAssistant::RegisterType(
            Closure::class,
            function (string $foo) : ? string { return '' === $foo ? null : $foo; },
            null,
            'foo'
        );
    }

    public function testRegisterTypeMustImplementMagicSetter() : void
    {
        static::assertTrue(DefinitionAssistant::IsTypeUnregistered(Closure::class));

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'Argument 1 passed to ' .
            BaseDefinitionAssistant::class .
            '::RegisterType() must declare __set() !'
        );

        DefinitionAssistant::RegisterType(
            Closure::class,
            null,
            function (string $foo) : ? string { return '' === $foo ? null : $foo; },
            'foo'
        );
    }

    /**
    * @psalm-param class-string $type
    *
    * @dataProvider DataProviderRegisterTypeSuccessFromArray
    *
    * @depends testRegisterTypeSuccess
    * @depends testRegisterTypeMustImplementMagicSetter
    */
    public function testRegisterTypeMustSpecifyAtLeastGetterOrSetter(
        string $type
    ) : void {
        DefinitionAssistant::ClearTypes();

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage(
            'One or both of arguments 2 and 3 must be specified!'
        );

        DefinitionAssistant::RegisterType($type, null, null, 'foo');
    }

    /**
    * @psalm-param class-string $type
    * @psalm-param null|Closure(string):?string $getter
    * @psalm-param null|Closure(string):?string $setter
    *
    * @dataProvider DataProviderRegisterTypeSuccessFromArray
    *
    * @depends testRegisterTypeMustSpecifyAtLeastGetterOrSetter
    * @depends testRegisterTypeMustImplementMagicSetter
    *
    * @psalm-suppress TooFewArguments
    */
    public function testRegisterTypeMustSpecifyAtLeastOneProperty(
        string $type,
        ? Closure $getter,
        ? Closure $setter
    ) : void {
        DefinitionAssistant::ClearTypes();

        static::expectException(ArgumentCountError::class);
        static::expectExceptionMessage(
            'Too few arguments to function ' .
            BaseDefinitionAssistant::class .
            '::RegisterType(), 3 passed in ' .
            __FILE__ .
            ' on line ' .
            (__LINE__ + 4) .
            ' and exactly 4 expected'
        );

        DefinitionAssistant::RegisterType($type, $getter, $setter);
    }

    /**
    * @depends testRegisterTypeSuccess
    */
    public function testCheckOtherSources() : void
    {
        DefinitionAssistant::ClearTypes();

        static::assertTrue(DefinitionAssistant::IsTypeUnregistered(
            Fixtures\ucwordsPrefixedImplementationChild::class
        ));

        static::assertSame([], DefinitionAssistant::ObtainExpectedProperties(
            Fixtures\ucwordsPrefixedImplementationChild::class
        ));

        static::assertNull(DefinitionAssistant::PublicCheckOtherTypesGetters(
            Fixtures\ucwordsPrefixedImplementationChild::class,
            'fooBar'
        ));

        static::assertNull(DefinitionAssistant::PublicCheckOtherTypesSetters(
            Fixtures\ucwordsPrefixedImplementationChild::class,
            'fooBar'
        ));

        foreach ($this->DataProviderRegisterTypeSuccessFromArray() as $args) {
            if (Fixtures\ucwordsPrefixedImplementation::class === ($args[0] ?? null)) {
                DefinitionAssistant::RegisterType($args[0], $args[1], $args[2], ...$args[3]);
            }
        }

        static::assertSame(['fooBar'], DefinitionAssistant::ObtainExpectedProperties(
            Fixtures\ucwordsPrefixedImplementation::class
        ));

        static::assertSame('GetFooBar', DefinitionAssistant::PublicCheckOtherTypesGetters(
            Fixtures\ucwordsPrefixedImplementationChild::class,
            'fooBar'
        ));

        static::assertSame('SetFooBar', DefinitionAssistant::PublicCheckOtherTypesSetters(
            Fixtures\ucwordsPrefixedImplementationChild::class,
            'fooBar'
        ));
    }
}
