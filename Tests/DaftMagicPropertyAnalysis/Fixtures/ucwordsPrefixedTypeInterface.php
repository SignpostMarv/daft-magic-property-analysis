<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests\DaftMagicPropertyAnalysis\Fixtures;

interface ucwordsPrefixedTypeInterface extends TypeInterface
{
    const PROPERTIES = [
        'fooBar',
    ];

    public function GetFooBar() : string;
}
