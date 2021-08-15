<?php

namespace Santeacademie\SuperUploaderBundle\Form\AssetType;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableEntityBridge;
use Santeacademie\SuperUploaderBundle\Event\VariantFormUpdateRequestEvent;
use Santeacademie\SuperUploaderBundle\Wrapper\TemporaryFile;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Form\AbstractAssetType;
use Santeacademie\SuperUploaderBundle\Form\AbstractVariantType;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableTemporaryBridge;
use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssetType extends AbstractAssetType
{

    public function __construct(
        private UploadableTemporaryBridge $uploadableTemporaryBridge,
        private UploadableEntityBridge $uploadableEntityBridge,
        private EventDispatcherInterface $eventDispatcher
    )
    {


    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (empty($options['uploadable_entity'])) {
            return;
        }

        /** @var AbstractAsset $asset */
        $asset = $options['uploadable_entity']->getUploadableAssetByName($builder->getForm()->getName());

        $builder->add('genuineFile', FileType::class, [
            'mapped' => false
        ]);

        foreach ($asset->getVariants() as $variant) {
            /** @var AbstractVariant $variant */
            $name = $variant->getName();

            $variantOptions = [
                'asset' => $asset,
                'variant' => $variant,
                'variant_upload_button' => $options['variant_upload_button'],
                'mapped' => false
            ];

            if (!is_null($options['label_variant'])) {
                $variantOptions['label'] = $options['label_variant'];
            }

            $builder->add($name, $variant->getVariantTypeClass(), $variantOptions);

            $variantFileOptions = [
                'mapped' => false
            ];

            if (!is_null($options['label_variant_file'])) {
                $variantFileOptions['label'] = $options['label_variant_file'];
            }

            $builder->get($name)
                ->add('temporaryFile', HiddenType::class, [
                    'mapped' => false
                ])
                ->add('variantFile', FileType::class, $variantFileOptions);

            /** @var AbstractVariantType $variantTypeInstance */
            $variantTypeInstance = $builder->getForm()->get($name)->getConfig()->getType()->getInnerType();

            if (!$variantTypeInstance->supportsVariant($variant)) {
                throw new \LogicException(sprintf('VariantType class "%s" doesn\'t support Variant of class "%s". Valid classes are [%s]',
                    get_class($variantTypeInstance),
                    $variant->getVariantTypeClass(),
                    implode(',', $variantTypeInstance->supportedVariants())
                ));
            }
        }


        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($asset) {
            $data = $event->getData();

            /** @var UploadableInterface $uploadableEntity */
            $uploadableEntity = $event->getForm()->getData();

            if (!$uploadableEntity instanceof UploadableInterface) {
                throw new \LogicException('Entity \'%s\' is not an \'UploadableInterface\' but using an \'AssetType\'', get_class($uploadableEntity));
            }

            foreach($asset->getVariants() as $variant) {
                /** @var AbstractVariant $variant */
                $oldValue = $variant->getVariantFile();

                $variantName = $variant->getName();

                /** @var AbstractVariant $variant */
                $variantTypeData = $data[$variantName];
                $variantTypeForm = $event->getForm()->get($variantName);

                $file = $variantTypeData['variantFile'] ?? $data['genuineFile'] ?? null;

                if (is_null($file)) {
                    if ($temporaryFileExists = !empty($variantTypeData['temporaryFile'] && is_file($variantTypeData['temporaryFile']))) {
                        // Simulate an uploaded File by taking the temporary one
                        $file = new TemporaryFile($variantTypeData['temporaryFile']);
                    }
                }

                if (is_null($file)) {
                    $oldFile = $this->uploadableEntityBridge->getEntityAssetVariantFile($uploadableEntity, $asset, $variant, false);

                    if (is_null($oldFile) && $variant->isRequired()) {
                        $event->getForm()->addError(new FormError(sprintf(
                            'Le document "%s" du champ "%s" est obligatoire',
                            $variant->getLabel(),
                            $asset->getLabel()
                        )));
                    }

                    continue;
                }

                if (!$file instanceof TemporaryFile) {
                    // Replace the uploaded File by taking the temporary one
                    $file = $this->uploadableTemporaryBridge->saveGenuineTemporaryFile($file);
                    $data[$variantName]['temporaryFile'] = $file->getPathname();
                }

                $data[$variantName]['variantFile'] = $file;

                $transformerCallback = function(AbstractVariant $variant, TemporaryFile $temporaryFile) use($variantTypeForm, $variantTypeData) {
                    // Transformations
                    /** @var AbstractVariantType $variantTypeClass */
                    $variantTypeClass = $variantTypeForm->getConfig()->getType()->getInnerType();

                    // Default "Identity" transformer (null transformer)
                    $transformedFile = $temporaryFile;

                    if (null !== $transformer = $variantTypeClass->getTransformer()) {
                        $transformedFile = $transformer->transformFile($temporaryFile, $variant, $variantTypeData);
                    }

                    return $transformedFile;
                };

                $temporaryVariantFile = $this->uploadableTemporaryBridge->genuineToTemporaryVariantFile($file, $variant, $uploadableEntity, $transformerCallback);

                // 2 fields (temporaryFile & variantFile) in variantType form means straightforward transformation with no customization from user,
                // so show transformed file instead (Related to @ShowNoTransformation)
                if ($variantTypeForm->count() == 2) {
                    $data[$variantName]['variantFile'] = $temporaryVariantFile;
                }

                $this->eventDispatcher->dispatch(new VariantFormUpdateRequestEvent($variant, $uploadableEntity, $variant->getVariantFile(), $temporaryVariantFile));
            }

            $event->setData($data);
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (empty($options['uploadable_entity'])) {
            return;
        }

        /** @var AbstractAsset $asset */
        $asset = $options['uploadable_entity']->getUploadableAssetByName($form->getName());

        $view->vars['asset'] = $asset;
        $view->vars['variants'] = $asset->getVariants();

        $view->vars['genuine_upload_button'] = $options['genuine_upload_button'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => UploadableInterface::class,
                'inherit_data' => true,
                'uploadable_entity' => null,
                'genuine_upload_button' => true,
                'variant_upload_button' => true,
                'label_variant' => null,
                'label_variant_file' => null,
                'required' => false,
                'mapped' => false
            ]
        );

        $resolver
            ->setAllowedTypes('uploadable_entity', ['null', UploadableInterface::class])
            ->setRequired('uploadable_entity')
            ->setAllowedTypes('label_variant', ['bool', 'string', 'null'])
            ->setAllowedTypes('label_variant_file', ['bool', 'string', 'null'])
            ->setAllowedTypes('genuine_upload_button', ['bool'])
            ->setAllowedTypes('variant_upload_button', ['bool']);
    }
}

