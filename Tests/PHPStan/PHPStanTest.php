<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Tests\PHPStan;

use Jean85\PrettyVersions;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase as Base;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class PHPStanTest extends Base
{
    public function testPHPStan() : void
    {
        $version = 'Version unknown';
        try {
            $version = PrettyVersions::getVersion('phpstan/phpstan')->getPrettyVersion();
        } catch (OutOfBoundsException $e) {
        }

        $application = new Application('PHPStan Checking', $version);
        $application->add(new AnalyseCommand());

        $command = $application->find('analyse');

        static::assertInstanceOf(AnalyseCommand::class, $command);

        $config = realpath(static::ObtainConfiguration());

        if (is_string($config)) {
            /**
            * @var AnalyseCommand
            */
            $command = $command;

            $command->overrideConfiguration($config);
        }

        $commandTester = new CommandTester($command);

        $commandTester->execute(
            [
                'paths' => [
                    __DIR__ . '/../../quick-checks/',
                ],
            ],
            [
                'capture_stderr_separately' => true,
            ]
        );
    }

    protected static function ObtainConfiguration() : string
    {
        return  __DIR__ . '/../../Tests/PHPStan/config.neon';
    }
}
