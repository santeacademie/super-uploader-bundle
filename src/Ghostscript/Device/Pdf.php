<?php

namespace Santeacademie\SuperUploaderBundle\Ghostscript\Device;

use Org_Heigl\Ghostscript\Device\DeviceInterface;

class Pdf implements DeviceInterface
{
    public const PDF_SETTINGS_SCREEN = '/screen';  // Very low size / quality
    public const PDF_SETTINGS_EBOOK = '/ebook'; // Low size / quality
    public const PDF_SETTINGS_PREPRESS = '/prepress'; // Medium size / quality
    public const PDF_SETTINGS_PRINTER = '/printer'; // Big size / quality
    public const PDF_SETTINGS_DEFAULT = '/default'; // Default size / quality

    public const PDF_SETTINGS = [
        self::PDF_SETTINGS_SCREEN,
        self::PDF_SETTINGS_EBOOK,
        self::PDF_SETTINGS_PREPRESS,
        self::PDF_SETTINGS_PRINTER,
        self::PDF_SETTINGS_DEFAULT
    ];

    private string $settings = self::PDF_SETTINGS_EBOOK;

    /**
     * Get the name of the device as Ghostscript expects it
     */
    public function getDevice(): string
    {
        return 'pdfwrite';
    }

    /**
     * Get the complete parameter string for this device
     */
    public function getParameterString(): string
    {
        $string = ' -sDEVICE=' . $this->getDevice();
        $string .= ' -dCompatibilityLevel=1.4 -dPDFSETTINGS='.$this->getSettings();

        return $string;
    }

    /**
     * Get the file ending
     */
    public function getFileEnding(): string
    {
        return 'pdf';
    }

    public function setSettings(string $settings): self
    {
        if (in_array($settings, self::PDF_SETTINGS, true)) {
            $this->settings = $settings;
        }

        return $this;
    }

    public function getSettings(): string
    {
        return  $this->settings;
    }
}