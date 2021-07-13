<?php
/**
 * @author JRK <jessym@santeacademie.com>
 */

namespace Santeacademie\SuperUploaderBundle\DependencyInjection\CompilerPass;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Bundle\TwigBundle\DependencyInjection\Configuration;

class TwigFormThemesPass implements CompilerPassInterface
{

    public static $THEMES = [
        '@SuperUploader/uploader/form/uploadable_asset.html.twig',
        '@SuperUploader/uploader/form/variant/document_variant_type.html.twig',
        '@SuperUploader/uploader/form/variant/video_variant_type.html.twig',
        '@SuperUploader/uploader/form/variant/imagick_crop_variant_type.html.twig',
        '@SuperUploader/uploader/form/variant/imagick_ugly_variant_type.html.twig'
    ];

    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('twig')) {
            return;
        }

        $container->setParameter('twig.form.resources', array_merge(
            $container->getParameter('twig.form.resources'),
            self::$THEMES
        ));
    }

}