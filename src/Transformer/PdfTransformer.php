<?php

namespace Santeacademie\SuperUploaderBundle\Transformer;

use Exception;
use Imagick;
use Org_Heigl\Ghostscript\Ghostscript;
use League\Flysystem\FilesystemOperator;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\Variant\PdfVariant;
use Santeacademie\SuperUploaderBundle\Ghostscript\Device\Pdf;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;
use Santeacademie\SuperUploaderBundle\Wrapper\SuperFile;
use Santeacademie\SuperUploaderBundle\Wrapper\TemporaryFile;
use Symfony\Component\HttpFoundation\File\File;

class PdfTransformer implements VariantTansformerInterface
{

    public function __construct(private FilesystemOperator $filesystemOperator)
    {
    }

    /**
     * @throws Exception
     */
    public function transformFile(SuperFile $file, PdfVariant|AbstractVariant $variant, array $variantTypeData): SuperFile
    {
        if ($file->guessExtension() !== PdfVariant::EXTENSION) {
            $imagick = new Imagick();
            $imagick->readImage($file->publicUrl());
            $imagick->setFilename($file->getBasename());

            $imagick->writeImage();

            $file = new TemporaryFile($file->getPath().'/'.$imagick->getFilename(), false);
        }

        if ((!is_null($variant->getSizeLimit()) && $file->getSize() > $variant->getSizeLimit())) {
            $gs = new Ghostscript();

            if ($variant->getGhostscriptPath() !== null) {
                $gs::setGsPath($variant->getGhostscriptPath());
            }

            $gs->setDevice(new Pdf());
            $gs->setInputFile($file->publicUrl());

            $compressedFileBaseName = sprintf(
                '%s_compressed',
                $file->getBasename('.'.$file->getExtension())
            );

            $gs->setOutputFile($compressedFileBaseName);

            $compressedFilename = $compressedFileBaseName.'.'.$file->getExtension();

            if ($gs->render()) {
                file_put_contents($file->publicUrl(), file_get_contents($file->getPath().'/'.$compressedFilename));
            } else {
                throw new Exception(
                    sprintf(
                        'Ghostscript error: render process has failed to write compressed file: "%s"',
                        $compressedFilename
                    )
                );
            }
        }

        return $file;
    }
}