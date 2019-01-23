<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests;

use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant;

function bat() : DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedTypeInterface {
    return new DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedImplementation();
}

$foo = bat();

$shouldBeTrue = '' === $foo->fooBar;

if ($foo instanceof DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedImplementation) {
    $foo->fooBar = 'baz';
}

$shouldBeTrue = 'baz' === $foo->fooBar;
