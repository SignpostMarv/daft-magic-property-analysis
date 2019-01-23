<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests;

use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant;

function bat() : DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedTypeInterface {
    return
        random_int(0, 1) === 1
            ? new DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedImplementation()
            : new DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedIdentical();
}

$foo = bat();

$shouldBeTrue = '' === $foo->fooBar;

if ($foo instanceof DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedImplementation) {
    $foo->fooBar = 'baz';
}

$shouldBeTrue = 'baz' === $foo->fooBar;
