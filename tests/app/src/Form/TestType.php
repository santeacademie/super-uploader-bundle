<?php

namespace Santeacademie\SuperUploaderBundle\Tests\App\Form;

use Santeacademie\SuperUploaderBundle\Form\AssetType\AssetType;
use Santeacademie\SuperUploaderBundle\Tests\App\Entity\TestEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('profile_picture', AssetType::class, [
                'uploadable_entity' => $builder->getData()
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => TestEntity::class ]);
    }
}
