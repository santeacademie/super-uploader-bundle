<?php

namespace Santeacademie\SuperUploaderBundle\Select;

use Santeacademie\SuperSelect\Annotation\SelectOption;
use Santeacademie\SuperSelect\AbstractSelect;

class SelectUploadMediaType extends AbstractSelect
{

    /**
     * @SelectOption(description="Image")
     */
    public static $PICTURE = 'picture';

    /**
     * @SelectOption(description="Vidéo")
     */
    public static $VIDEO = 'video';

    /**
     * @SelectOption(description="Document")
     */
    public static $DOCUMENT = 'document';

    /**
     * @SelectOption(description="Other")
     */
    public static $OTHER = 'other';

}