<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests\DaftMagicPropertyAnalysis\Fixtures;

interface TypeInterface
{
    /**
    * @return scalar|array|object|null
    */
    public function __get(string $k);

    /**
    * @param scalar|array|object|null $v
    */
    public function __set(string $k, $v) : void;
}
