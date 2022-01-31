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

- Install assets
```bash
php bin/console assets:install
````

- Install jQuery
```html
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
```

- Install Ghostscript
```bash
sudo apt-get install ghostscript
```

## 2. Usage

- Entity example (`public` is important on asset attribute !)

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

- Form view dependencies
```html
<link rel="stylesheet" href="{{ asset('bundles/superuploader/css/uploader/form/uploadable_asset.css') }}"/>
<script src="{{ asset('bundles/superuploader/js/uploader/form/uploadable_asset.js') }}"></script>
```

- Additional dependencies for ImagickCrop variant

```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css" integrity="sha512-zxBiDORGDEAYDdKLuYU9X/JaJo/DPzE42UubfBw9yg8Qvb2YRRIQ8v4KsGHOx2H1/+sdSXyXxLXv5r7tHc9ygg==" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js" integrity="sha512-Gs+PsXsGkmr+15rqObPJbenQ2wB3qYvTHuJO6YJzPe/dTLvhy0fmae2BcnaozxDo5iaF8emzmCZWbQ1XXiX2Ig==" crossorigin="anonymous"></script>
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
