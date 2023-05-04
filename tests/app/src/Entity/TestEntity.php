<?php

namespace Santeacademie\SuperUploaderBundle\Tests\App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Santeacademie\SuperUploaderBundle\Annotation\UploadableField;
use Santeacademie\SuperUploaderBundle\Annotation\UploadableKey;
use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Trait\UploadableTrait;
#[ORM\Entity]

class TestEntity implements UploadableInterface
{
    use UploadableTrait;

    /**
     * @UploadableKey
     */

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private string $id;
    private string $name;

    /**
     * @UploadableField(name="profile_picture", asset="Santeacademie\SuperUploaderBundle\Tests\App\Asset\TestAsset")
     */
    public ?AbstractAsset $profilePicture = null;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

}