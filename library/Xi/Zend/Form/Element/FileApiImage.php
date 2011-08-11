<?php

namespace Xi\Zend\Form\Element;

/**
 * An extension for FileApiFile that displays added image files.
 * 
 * Thus far only for new uploads, doesn't show old values.
 */
class FileApiImage extends FileApiFile
{
    public function init()
    {
        $this->setLoadCallbackScript(<<<EOF
            var image = new Image();
            image.src = loadEvent.target.result;
            jQuery('.file-api-file[data-name="' + elementName + '"]').prepend(image);
EOF
        );
        parent::init();
    }
}