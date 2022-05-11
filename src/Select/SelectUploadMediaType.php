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
     * @SelectOption(description="PDF")
     */
    public static $DOCUMENT = 'document';

    /**
     * @SelectOption(description="Autre")
     */
    public static $GENERIC_FILE = 'generic_file';

}
