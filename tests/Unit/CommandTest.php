<?php

namespace Unit;

use League\Flysystem\FilesystemOperator;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableEntityBridge;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTest extends KernelTestCase
{

    public function testCommand()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $container = static::getContainer();
        /* @var FilesystemOperator $filesytem */
        $filesytem = $container->get('super_uploader.flysystem.resources');

        $command = $application->find('santeacademie:super-uploader:generate:fallbacks');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);


        $commandTester->assertCommandIsSuccessful();

        $this->assertTrue($filesytem->fileExists('/testentity-0888da/picture/profile_picture/profile_picture-landscape.png'));
        $this->assertTrue($filesytem->fileExists('/testentity-0888da/picture/profile_picture/profile_picture-portrait.png'));
        $filesytem->deleteDirectory('/');
    }

}