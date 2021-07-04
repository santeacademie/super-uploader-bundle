<?php 

namespace Santeacademie\SuperUploaderBundle\Trait;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Annotation\UploadableField;
use Doctrine\Common\Annotations\AnnotationReader;
use Santeacademie\SuperUtil\PathUtil;
use Symfony\Component\HttpFoundation\File\File;

trait UploadableTrait
{

    private $uploadableFields = [];

    private function readUploadableAnnotations(): array
    {
        $reflectionClass = new \ReflectionClass(get_called_class());

        $annotations = array_map(function($scope) use($reflectionClass) {
            $props = $reflectionClass->getProperties($scope);
            $reader = new AnnotationReader();

            return array_filter(array_map(function (\ReflectionProperty $prop) use ($reflectionClass, $reader, $scope) {
                $uploadableAnnotation = $reader->getPropertyAnnotation(
                    $reflectionClass->getProperty($prop->getName()),
                    UploadableField::class
                );

                return $uploadableAnnotation ? [
                    'class' => $reflectionClass->getName(),
                    'scope' => $scope,
                    'property' => $prop->getName(),
                    'value' => $this->{$prop->getName()},
                    'annotation' => $uploadableAnnotation
                ] : false;
            }, $props));
        }, [\ReflectionProperty::IS_PUBLIC, \ReflectionProperty::IS_PRIVATE]);

        return array_merge([], ...$annotations);
    }

    public function getLoadUploadableAssets(): array
    {
        if (!empty($this->uploadableFields)) {
            return $this->uploadableFields;
        }

        $this->uploadableFields = array_reduce($this->readUploadableAnnotations(), function ($carry, $uploadableField) {
            /** @var AbstractAsset $asset */
            $asset = $uploadableField['annotation']->getAsset();

            $asset->setName($uploadableField['annotation']->getName() ?? $uploadableField['property']);
            $asset->setPropertyName($uploadableField['property']);

            if ($uploadableField['scope'] == \ReflectionProperty::IS_PUBLIC) {
                $asset->setPropertyScope(AbstractAsset::PROPERTY_SCOPE_PUBLIC);
            } else {
                $asset->setPropertyScope(AbstractAsset::PROPERTY_SCOPE_PRIVATE);
            }

            $carry[$asset->getName()] = $asset;

            return $carry;
        }, []);

        return $this->uploadableFields;
    }

    public function getUploadableAssetByName(string $assetName): AbstractAsset
    {
        $assets = $this->getLoadUploadableAssets();

        if (!isset($assets[$assetName])) {
            throw new \LogicException(sprintf('Asset "%s" doesn\'t exist for "%s". Possible values are [%s]',
                $assetName,
                get_called_class(),
                implode(',', array_keys($assets))
            ));
        }

        return $assets[$assetName];
    }

    public function getUploadEntityPath(): string
    {
        $reflectionClass = new \ReflectionClass(get_called_class());

        $className = PathUtil::sanitizeForProxy($reflectionClass->getName());

        $entityName = strtolower($reflectionClass->getShortName());
        $identifier = strtolower(substr(md5($className),0,6));
        $directory = sprintf('%s-%s', $entityName, $identifier);
        
        return $directory;
    }

    public function getUploadEntityToken(): ?string
    {
        return implode('/', str_split($this->getEntityIdentifierValue()));
    }



}