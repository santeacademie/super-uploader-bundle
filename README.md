Super Uploader Bundle
=

## 1. Install

- Execute composer command
```bash
    composer require santeacademie/super-uploader-bundle
```

- Configure `nano config/packages/super_uploader.yaml`
```yaml
# config/packages/super_uploader.yaml
super_uploader:
    mountpoints:
        uploads: 'uploads'
        resources: 'resources'
        temp: 'uploads/tmp'

    # Configures different persistence methods that can be used by the bundle for saving variant entity map.
    # Only one persistence method can be configured at a time.
    # You can disable persistence by commenting all persistence methods
    persistence: # Required
        doctrine:
            # Name of the entity manager that you wish to use for managing variant entity map.
            entity_manager: default
            # table_name: super_uploader_variant_entity_map
            # schema_name: public
        # in_memory: ~

    variant_entity_map:
        # Set a custom variant entity map class. Must be a Santeacademie\SuperUploaderBundle\Model\VariantEntityMap
        classname: Santeacademie\SuperUploaderBundle\Model\VariantEntityMap
```

## 2. Usage

- Entity example

```php
<?php

namespace App\Foo\Entity;

use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Trait\UploadableTrait;
use Santeacademie\SuperUploaderBundle\Annotation\UploadableField;
use Santeacademie\SuperUploaderBundle\Annotation\UploadableKey;

class User implements UploadableInterface
{
    use UploadableTrait;

    /**
     * @UploadableKey
     */
    private string $id;
    private string $firstname;
    private string $lastname;
    private string $email;
    
    /**
     * @UploadableField(name="profile_picture", asset="App\Foo\Asset\ProfilePictureAsset")
     */
    public ?AbstractAsset $profilePicture = null;
    
    public function getEmail(): string 
    {
        return $this->email;
    }
?>
```

- Asset example

```php
<?php

namespace App\Foo\Asset;

use Santeacademie\SuperUploaderBundle\Form\VariantType\ImagickCropVariantType;
use Santeacademie\SuperUploaderBundle\Select\SelectUploadMediaType;
use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Asset\Variant\PictureVariant;

class ProfilePictureAsset extends AbstractAsset
{

    const VARIANT_PORTRAIT = 'portrait';
    const VARIANT_LANDSCAPE = 'landscape';

    public function getLabel(): string
    {
        return 'Profile picture';
    }

    public function supportedVariants(): array
    {
        return [
            new PictureVariant(
                variantTypeClass: ImagickCropVariantType::class,
                required:false,
                name: self::VARIANT_PORTRAIT,
                label: 'Portrait',
                width: 595,
                height: 895,
                extension: 'png'
            ),
            new PictureVariant(
                variantTypeClass: ImagickCropVariantType::class,
                required: false,
                name: self::VARIANT_LANDSCAPE,
                label: 'Landscape',
                width: 365,
                height: 298,
                extension: 'png'
            ),
        ];
    }

    public function getMediaType(): string
    {
        return SelectUploadMediaType::$PICTURE;
    }

}
?>
```


- Form type example

```php
<?php

namespace App\Foo\Form;

use Santeacademie\SuperUploaderBundle\Form\AssetType\AssetType;
use App\Foo\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('email', EmailType::class)
            ->add('trainer_photo', AssetType::class, [
                'uploadable_entity' => $builder->getData()
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class ]);
    }
}
?>
```

## 3. Commands

- Generate placeholders

```bash
php bin/console santeacademie:super-uploader:generate:fallbacks
```

- Rebuild variant entity map into database

```bash
php bin/console santeacademie:super-uploader:generate:dbmap
```

## 4. Useful

- Upload file programmatically

```php
<?php

namespace App\Foo\Controller;

use App\Foo\Entity\User;
use App\Foo\Asset\ProfilePictureAsset;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableEntityBridge;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;use Symfony\Component\Routing\Annotation\Route;

class FooController extends AbstractController 
{

      /**
     * @Route("/foo/{id}", name="foo", methods={"POST"})
     */
    public function foo(Request $request, User $user, UploadableEntityBridge $uploadableEntityBridge) 
    {
        $variantUser = $user->profilePicture->getVariant(ProfilePictureAsset::VARIANT_LANDSCAPE);
        
        $uploadableEntityBridge->manualUpload(
            entity: $registration, 
            variant: $variantRegistration, 
            fileOrBinary: $request->files->get('profile_picture')
        );

        return new Response(sprintf('Profile picture uploaded for user %s !', $user->getEmail()));
    }

}

?>
```

- Generate asset in twig
```html
<img src="{{ asset(user.profilePicture.variant('landscape').variantFile) }}" />
```