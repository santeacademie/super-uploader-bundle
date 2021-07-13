<?php 

namespace Santeacademie\SuperUploaderBundle\Bridge;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;

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

    public function getVariantFileName(AbstractVariant $variant, string $extension = '', string $suffix = '')
    {
        // trainer_profile-rectangle[suffix][.extension]
        return sprintf('%s-%s%s%s',
            $variant->getAsset()->getName(),
            $variant->getName(),
            empty($suffix)      ? '' : '-'.$suffix,
            empty($extension)   ? '' : '.'.$extension
        );
    }



}