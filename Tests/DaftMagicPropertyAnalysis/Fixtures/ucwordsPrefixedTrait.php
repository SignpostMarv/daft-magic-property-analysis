<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests\DaftMagicPropertyAnalysis\Fixtures;

trait ucwordsPrefixedTrait
{
    public function GetFooBar() : string
    {
        $out = $this->__get('fooBar');

        if (is_scalar($out) || is_null($out)) {
            return (string) $out;
        }

        return var_export($out, true);
    }

    public function SetFooBar(string $v) : void
    {
        $this->__set('fooBar', $v);
    }
}
