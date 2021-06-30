<?php

namespace Santeacademie\SuperUploaderBundle\Form;

use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\Variant\PictureVariant;
use Santeacademie\SuperUploaderBundle\Super\Interfaces\VariantTansformerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractVariantType extends AbstractType
{
    abstract public function getTransformer(): ?VariantTansformerInterface;

    abstract public function supportedVariants(): array;

    private function supportsVariantClass(string $variantClass): bool
    {
        foreach($this->supportedVariants() as $supportedVariantClass) {
            if (!isset(class_parents($supportedVariantClass)[AbstractVariant::class])) {
                throw new \LogicException(sprintf('Class "%s" is not a valid supportable variant class (not an AbstractVariant class)',
                    $supportedVariantClass
                ));
            }

            if ($variantClass == $supportedVariantClass) {
                return true;
            }
        }

        return false;
    }

    public function supportsVariant(AbstractVariant $variant): bool
    {
        return $this->supportsVariantClass(get_class($variant));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var AbstractAsset $asset */
        $asset = $options['asset'];
        $view->vars['asset'] = $asset;

        /** @var PictureVariant $variant */
        $variant = $options['variant'];
        $view->vars['variant'] = $variant;

        $view->vars['variant_upload_button'] = $options['variant_upload_button'];
        $view->vars['js'] = $options['js'];
        $view->vars['css'] = $options['css'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'variant_upload_button' => true,
                'inherit_data' => true,
                'asset' => null,
                'variant' => null,
                'js' => false,
                'css' => false
            ]
        );

        $resolver
            ->setAllowedTypes('asset', [AbstractAsset::class])
            ->setRequired('asset')
            ->setAllowedTypes('variant', $this->supportedVariants())
            ->setRequired('variant')
            ->setAllowedTypes('variant_upload_button', ['bool'])
            ->setAllowedTypes('js', ['bool'])
            ->setAllowedTypes('css', ['bool'])
        ;
    }
}