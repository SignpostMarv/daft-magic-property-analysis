<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests\DaftMagicPropertyAnalysis\Fixtures;

class ucwordsPrefixedImplementation extends AbstractImplementation implements ucwordsPrefixedTypeInterface
{
    use ucwordsPrefixedTrait;
}
