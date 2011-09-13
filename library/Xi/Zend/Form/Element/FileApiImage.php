<?php

namespace Xi\Zend\Form\Element;

/**
 * An extension for FileApiFile that displays added image files.
 * 
 * When editing, existing values can be shown using
 * setImagePresentation/setFilePresentation methods.
 */
class FileApiImage extends FileApiFile
{
    
    /**
     * Sets a visible presentation of an existing value using HTML image tag.
     * 
     * @param string $url       Publicly available path to image.
     * @param string $class     CSS class definitions.
     */
    public function setImagePresentation($url, $class = null)
    {
        return $this->setFilePresentation('<img src="' . $url . '" class="' . $class . '"/>');
    }
    
    public function init()
    {
        $this->setLoadCallbackScript(<<<EOF
            var image = new Image();
            image.src = loadEvent.target.result;
            jQuery('.file-api-file[data-name="' + elementName + '"]').html(image);
EOF
        );
        
        parent::init();
    }
}