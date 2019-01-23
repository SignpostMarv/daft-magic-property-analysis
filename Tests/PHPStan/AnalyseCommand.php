<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests\PHPStan;

use PHPStan\Command\AnalyseCommand as Base;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyseCommand extends Base
{
    /**
    * @var string|null
    */
    protected $overriden_configuration;

    public function overrideConfiguration(string $config) : void
    {
        $this->overriden_configuration = $config;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        if (is_string($this->overriden_configuration)) {
            $input->setOption('configuration', $this->overriden_configuration);
        }

        return parent::execute($input, $output);
    }
}
