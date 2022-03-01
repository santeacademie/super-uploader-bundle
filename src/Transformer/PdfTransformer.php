<?php

namespace Santeacademie\SuperUploaderBundle\Transformer;

use Exception;
use Imagick;
use Org_Heigl\Ghostscript\Ghostscript;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\Variant\PdfVariant;
use Santeacademie\SuperUploaderBundle\Ghostscript\Device\Pdf;
use Santeacademie\SuperUploaderBundle\Interface\VariantTansformerInterface;
use Symfony\Component\HttpFoundation\File\File;

class PdfTransformer implements VariantTansformerInterface
{
    /**
     * @throws Exception
     */
    public function transformFile(File $file, PdfVariant|AbstractVariant $variant, array $variantTypeData): File
    {
        if ($file->guessExtension() !== PdfVariant::EXTENSION) {
            $imagick = new Imagick();
            $imagick->readImage($file->getRealPath());
            $imagick->setFilename($file->getBasename());

            $imagick->writeImage();

            $file = new File($file->getBasename());
        }

        if ((!is_null($variant->getSizeLimit()) && $file->getSize() > $variant->getSizeLimit())) {
            $gs = new Ghostscript();

            if ($variant->getGhostscriptPath() !== null) {
                $gs::setGsPath($variant->getGhostscriptPath());
            }

            $gs->setDevice(new Pdf());
            $gs->setInputFile($file->getRealPath());

            $compressedFileBaseName = sprintf(
                '%s_compressed',
                $file->getBasename('.'.$file->getExtension())
            );

            $gs->setOutputFile($compressedFileBaseName);

            $compressedFilename = $compressedFileBaseName.'.'.$file->getExtension();

            if ($gs->render()) {
                file_put_contents($file->getRealPath(), file_get_contents($file->getPath().'/'.$compressedFilename));
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