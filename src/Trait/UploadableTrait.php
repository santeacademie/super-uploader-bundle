<?php

namespace Santeacademie\SuperUploaderBundle\Trait;

use Santeacademie\SuperUploaderBundle\Annotation\UploadableKey;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Annotation\UploadableField;
use Doctrine\Common\Annotations\AnnotationReader;
use Santeacademie\SuperUtil\PathUtil;
use Santeacademie\SuperUtil\StringUtil;
use Symfony\Component\HttpFoundation\File\File;

trait UploadableTrait
{

    private static $uploadableKey = null;
    private static $uploadableKeySplitLength = UploadableKey::DEFAULT_SPLIT_LENGTH;
    private $uploadableFields = [];

    public function getUploadableKeyName(): string
    {
        if (empty(self::$uploadableKey)) {
            $this->readUploadableKeyAnnotation();
        }

        return self::$uploadableKey;
    }

    public function getUploadableKeyValue(): ?string
    {
        $keyName = $this->getUploadableKeyName();

        $keyGetter = 'get'.StringUtil::anyToCamelCase($keyName, true);

        if (!method_exists($this, $keyGetter)) {
            throw new \LogicException(sprintf('UploadableKey \'%s\' doesn\'t have getter method \'%s()\' on class \'%s\'', $keyName, $keyGetter, get_class($this)));
        }

        return $this->{$keyGetter}();
    }

    private function readUploadableKeyAnnotation(): void
    {
        $reflectionClass = new \ReflectionClass(PathUtil::sanitizeForProxy(get_called_class()));
        $annotations = [];

        foreach([\ReflectionProperty::IS_PUBLIC, \ReflectionProperty::IS_PRIVATE] as $scope) {
            $props = $reflectionClass->getProperties($scope);
            $reader = new AnnotationReader();

            foreach($props as $prop) {
                $uploadableAnnotation = $reader->getPropertyAnnotation(
                    $reflectionClass->getProperty($prop->getName()),
                    UploadableKey::class
                );

                if ($uploadableAnnotation) {
                    $annotations[] = [
                        'class' => $reflectionClass->getName(),
                        'scope' => $scope,
                        'property' => $prop->getName(),
                        'value' => $this->{$prop->getName()},
                        'annotation' => $uploadableAnnotation
                    ];
                }
            }
        }

        if (count($annotations) > 1) {
            throw new \LogicException(sprintf('You must have only 1 \'%s\' annotation on class \'%s\'', UploadableKey::class, get_class($this)));
        }

        if (empty($annotations)) {
            self::$uploadableKey = 'id';
            self::$uploadableKeySplitLength = UploadableKey::DEFAULT_SPLIT_LENGTH;
        } else {
            self::$uploadableKey = $annotations[0]['property'];
            self::$uploadableKeySplitLength = $annotations[0]['annotation']->getSplitLength();
        }
    }

    private function readUploadableFieldAnnotations(): array
    {
        $reflectionClass = new \ReflectionClass(get_called_class());
        $annotations = [];

        foreach([\ReflectionProperty::IS_PUBLIC, \ReflectionProperty::IS_PRIVATE] as $scope) {
            $props = $reflectionClass->getProperties($scope);
            $reader = new AnnotationReader();

            foreach($props as $prop) {
                $uploadableAnnotation = $reader->getPropertyAnnotation(
                    $reflectionClass->getProperty($prop->getName()),
                    UploadableField::class
                );

                if ($uploadableAnnotation) {
                    $annotations[] = [
                        'class' => $reflectionClass->getName(),
                        'scope' => $scope,
                        'property' => $prop->getName(),
                        'value' => $this->{$prop->getName()},
                        'annotation' => $uploadableAnnotation
                    ];
                }
            }
        }

        return $annotations;
    }

    public function getLoadUploadableAssets(): array
    {
        if (!empty($this->uploadableFields)) {
            return $this->uploadableFields;
        }

        $this->uploadableFields = array_reduce($this->readUploadableFieldAnnotations(), function ($carry, $uploadableField) {
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
        $uploadableKeyValue = $this->getUploadableKeyValue();

        return implode('/', str_split($uploadableKeyValue, self::$uploadableKeySplitLength));
    }



}
