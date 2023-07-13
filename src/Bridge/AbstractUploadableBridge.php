<?php

namespace Santeacademie\SuperUploaderBundle\Bridge;

use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUtil\StringUtil;

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
        // trainer_profile-rectangle[hashed_suffix][.extension]
        return sprintf('%s-%s%s%s',
            $variant->getAsset()->getName(),
            $variant->getName(),
            empty($suffix)      ? '' : '-'.md5($suffix),
            empty($extension)   ? '' : '.'.$extension
        );
    }



}
