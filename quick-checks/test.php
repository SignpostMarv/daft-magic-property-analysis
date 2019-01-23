<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests;

function bat() : DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedTypeInterface
{
    return
        1 === random_int(0, 1)
            ? new DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedImplementation()
            : new DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedIdentical();
}

$foo = bat();

$shouldBeTrue = '' === $foo->fooBar;

if ($foo instanceof DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedImplementation) {
    $foo->fooBar = 'baz';
}

$shouldBeTrue = 'baz' === $foo->fooBar;
