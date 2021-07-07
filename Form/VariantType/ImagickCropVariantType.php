<?php

namespace Santeacademie\SuperUploaderBundle\Form\VariantType;

use Santeacademie\SuperUploaderBundle\Asset\Variant\PictureVariant;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;
use Santeacademie\SuperUploaderBundle\Form\AbstractVariantType;
use Santeacademie\SuperUploaderBundle\Transformer\ImagickCropTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type;

class ImagickCropVariantType extends AbstractVariantType
{


    public function __construct(
        private ImagickCropTransformer $transformer
    )
    {

    }

    public function getTransformer(): VariantTansformerInterface
    {
        return $this->transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $class = HiddenType::class;
        //$class = TextType::class;
        $builder
            ->add('zoom', $class, [
                'constraints' => [new Type(['type' => 'numeric'])],
                'mapped' => false
            ])
            ->add('topLeftX', $class, [
                'constraints' => [new Type(['type' => 'numeric'])],
                'mapped' => false
            ])
            ->add('topLeftY', $class, [
                'constraints' => [new Type(['type' => 'numeric'])],
                'mapped' => false
            ])
            ->add('bottomRightX', $class, [
                'constraints' => [new Type(['type' => 'numeric'])],
                'mapped' => false
            ])
            ->add('bottomRightY', $class, [
                'constraints' => [new Type(['type' => 'numeric'])],
                'mapped' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('js', true);
        $resolver->setDefault('css', true);
    }

    public function supportedVariants(): array
    {
        return [
            PictureVariant::class
        ];
    }

}
