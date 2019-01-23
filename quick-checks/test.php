<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests;

use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant;

$getter = function (string $property) : ? string {
    return
        in_array(
            $property,
            DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedTypeInterface::PROPERTIES,
            true
        )
            ? ('Get' . ucwords($property))
            : null;
};

$setter = function (string $property) : ? string {
    return
        in_array(
            $property,
            DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedTypeInterface::PROPERTIES,
            true
        )
            ? ('Set' . ucwords($property))
            : null;
};

DefinitionAssistant::RegisterType(
    DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedTypeInterface::class,
    $getter,
    $setter,
    ...DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedTypeInterface::PROPERTIES
);

DefinitionAssistant::RegisterType(
    DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedImplementation::class,
    $getter,
    $setter,
    ...DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedTypeInterface::PROPERTIES
);

function bat() : DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedTypeInterface {
    return new DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedImplementation();
}

$foo = bat();

$shouldBeTrue = '' === $foo->fooBar;

if ($foo instanceof DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedImplementation) {
    $foo->fooBar = 'baz';
}

$shouldBeTrue = 'baz' === $foo->fooBar;
