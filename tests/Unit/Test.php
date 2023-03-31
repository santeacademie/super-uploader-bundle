<?php

namespace Unit;

use League\Flysystem\FilesystemOperator;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableEntityBridge;
use Santeacademie\SuperUploaderBundle\Tests\App\Asset\TestAsset;
use Santeacademie\SuperUploaderBundle\Tests\App\Entity\TestEntity;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class Test extends KernelTestCase
{
    public function testOk(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        /* @var UploadableEntityBridge $uploadableEntityBridge */
        $uploadableEntityBridge = $container->get(UploadableEntityBridge::class);
        /* @var FilesystemOperator $filesytem */
        $filesytem = $container->get('super_uploader.flysystem.uploads');



        $user = new TestEntity();
        $user->setId(1);

        $uploadableEntityBridge->populateUploadableFields($user);


        $landscapeVariant = $user->profilePicture->getVariant(TestAsset::VARIANT_LANDSCAPE);
        $portraitVariant = $user->profilePicture->getVariant(TestAsset::VARIANT_PORTRAIT);

        $projectDirectory = $container->getParameter('kernel.project_dir');

        $uploadableEntityBridge->manualUpload(
            entity: $user,
            variant: $landscapeVariant,
            fileOrBinary: file_get_contents($projectDirectory . '/storage/lockscreen.png')
        );

        $uploadableEntityBridge->manualUpload(
            entity: $user,
            variant: $portraitVariant,
            fileOrBinary: file_get_contents($projectDirectory . '/storage/lockscreen.png')
        );


        $this->assertEquals(count($filesytem->listContents('testentity-0888da/1/picture/profile_picture/')->toArray()), 2);

        $this->assertNotNull($user->profilePicture->getVariant('landscape')->getVariantFile());
        $this->assertNotNull($user->profilePicture->getVariant('portrait')->getVariantFile());

    }

}
