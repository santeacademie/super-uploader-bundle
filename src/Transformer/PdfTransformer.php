<?php

namespace Santeacademie\SuperUploaderBundle\Transformer;

use Exception;
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
        if (
            $file->guessExtension() !== PdfVariant::EXTENSION
            || (!is_null($variant->getSizeLimit()) && $file->getSize() > $variant->getSizeLimit())
        ) {
            $gs = new Ghostscript();

            if (!$variant->getGhostscriptPath() === null) {
                $gs::setGsPath($variant->getGhostscriptPath());
            }

            $gs->setDevice(new Pdf());
            $gs->setInputFile($file->getRealPath());

            $compressedFile = sprintf('%s_compressed.%s',
                $file->getBasename('.'.$file->getExtension()),
                $file->getExtension()
            );

            $gs->setOutputFile($compressedFile);

            if ($gs->render()) {
                file_put_contents($file->getRealPath(), file_get_contents($file->getPath().'/'.$compressedFile));
            } else {
                throw new Exception(
                    sprintf(
                        'Ghostscript error: render process has failed to write compressed file: "%s"',
                        $compressedFile
                    )
                );
            }
        }

        return $file;
    }
}