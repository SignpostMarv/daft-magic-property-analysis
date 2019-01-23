<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests\DaftMagicPropertyAnalysis\Fixtures;

abstract class AbstractImplementation
{
    /**
    * @var array<string, scalar|array|object|null>
    */
    protected $data = [];

    /**
    * @return scalar|array|object|null
    */
    public function __get(string $k)
    {
        return $this->data[$k] ?? null;
    }

    /**
    * @param scalar|array|object|null $v
    */
    public function __set(string $k, $v) : void
    {
        $this->data[$k] = $v;
    }

    public function __isset(string $k) : bool
    {
        return isset($this->data[$k]);
    }

    public function __unset(string $k) : void
    {
        unset($this->data[$k]);
    }
}
