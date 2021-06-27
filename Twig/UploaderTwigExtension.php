<?php
/**
 * @author JRK <jessym@santeacademie.com>
 */

namespace Santeacademie\SuperUploaderBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class UploaderTwigExtension extends AbstractExtension implements GlobalsInterface
{

    public function __construct(private string $twigGlobalsEnabled)
    {
        if (!$twigGlobalsEnabled) {
            return;
        }
    }

    public function getGlobals(): array
    {
        $globals = [];

        return $globals;
    }


}