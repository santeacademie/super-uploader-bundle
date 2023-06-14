<?php

namespace Santeacademie\SuperUploaderBundle\Tests\Unit;

use Santeacademie\SuperUploaderBundle\Tests\App\Entity\TestEntity;
use Santeacademie\SuperUploaderBundle\Tests\App\Form\TestType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

class FormTest extends KernelTestCase
{

    public function testForm()
    {
        self::bootKernel();
        $container = static::getContainer();
        /* @var FormFactoryInterface $formFactory */
        $formFactory = $container->get('form.factory');

        $entity = new TestEntity();
        $entity->setId(2);

        $form = $formFactory->create(TestType::class, $entity);
        $projectDirectory = $container->getParameter('kernel.project_dir');

        $form->submit([
            'profile_picture' => [
                'portrait' => [
                    "zoom" => "0.4608",
                    "topLeftX" => "109",
                    "topLeftY" => "-4",
                    "bottomRightX" => "550",
                    "bottomRightY" => "655",
                    "temporaryFile" => file_get_contents($projectDirectory . '/storage/lockscreen.png'),
                    "variantFile" => null,
                ],
                'landscape' => [
                    "zoom" => "0.4608",
                    "topLeftX" => "109",
                    "topLeftY" => "-4",
                    "bottomRightX" => "550",
                    "bottomRightY" => "655",
                    "temporaryFile" => '',
                    "variantFile" => null,

                ],
            ]
        ]);


        $this->assertTrue($form->isSynchronized());

        $this->assertTrue(true);
    }

}