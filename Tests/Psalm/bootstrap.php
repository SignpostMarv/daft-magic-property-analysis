<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests\Psalm;

use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant;
use SignpostMarv\DaftMagicPropertyAnalysis\Tests\DaftMagicPropertyAnalysis;

require_once(__DIR__ . '/../../vendor/autoload.php');

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
