<?php 

namespace Santeacademie\SuperUploaderBundle\Bridge;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;

abstract class AbstractUploadableBridge
{

    protected $absolutePublicDirEnabled = false;

    public function __construct(
        protected string $appPublicDir
    ) {

    }

    public function isAbsolutePublicDirEnabled(): bool
    {
        return $this->absolutePublicDirEnabled;
    }

    public function setAbsolutePublicDirEnabled(bool $absolutePublicDirEnabled): self
    {
        $this->absolutePublicDirEnabled = $absolutePublicDirEnabled;

        return $this;
    }

    public function getPublicDir(): string
    {
        return $this->isAbsolutePublicDirEnabled() ? $this->appPublicDir.'/' : '';
    }

    public function getVariantFileName(AbstractVariant $variant, UploadableInterface $uploadableEntity = null, string $extension = ''): string
    {
        // trainer_profile-rectangle[-hash][.extension]
        return sprintf('%s-%s%s%s',
            $variant->getAsset()->getName(),
            $variant->getName(),
            empty($uploadableEntity) ? '' : '-' . md5(sprintf('%s-%s-%s', $variant->getAsset()->getName(), $variant->getName(), $uploadableEntity->getUploadableKeyValue())),
            empty($extension)        ? '' : '.' . $extension,
        );
    }



}
