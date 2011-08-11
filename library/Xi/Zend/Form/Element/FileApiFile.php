<?php

namespace Xi\Zend\Form\Element;

/**
 * A form element for uploading files from FileAPI capable browsers.
 * 
 * Assumes jQuery.
 * 
 * You'll need to style the div for it to be visible. Something like this:
 * 
 * div.file-api-file {
 *     border: 1px solid #999;
 *     width: 32px;
 *     height: 32px;
 *     background: white;
 * }
 *     div.file-api-file.over {
 *         background: #aaa;
 *     }
 * 
 * Remember to indicate clearly that one is supposed to drag files on this
 * element.
 * 
 * Doesn't implement a normal file upload fallback.
 * 
 */
class FileApiFile extends \Zend_Form_Element_Hidden
{
    protected $_scriptLocation;
    protected $_maximumFileSize;
    protected $_loadCallbackScript;
    
    public function setScriptLocation($location = 'head')
    {
        $this->_scriptLocation = $location;
        
        return $this;
    }
    public function getScriptLocation()
    {
        return $this->_scriptLocation ?: 'head';
    }
    
    public function setMaximumFileSize($size)
    {
        $this->_maximumFileSize = $size;
        
        return $this;
    }
    public function getMaximumFileSize()
    {
        return $this->_maximumFileSize ?: 10240; // 10KiB
    }
    
    public function setLoadCallbackScript($script)
    {
        $this->_loadCallbackScript = $script;
        
        return $this;
    }
    public function getLoadCallbackScript()
    {
        return $this->_loadCallbackScript;
    }
    
    public function getFileData()
    {
        $parts = explode(',', $this->getValue());
        if (count($parts) === 2 && strpos($parts[0], 'base64') !== false) {
            return base64_decode($parts[1]);
        }
    }
    
    public function init()
    {
        parent::init();
        
        $this->clearDecorators();
        
        $this->addDecorator(new \Zend_Form_Decorator_Callback(array('callback' => function($content, $element, $options) {
            return '<div class="file-api-file" data-name="' . $element->getName() . '"></div>'
                 . '<input type="hidden" name="' . $element->getName() . '" />';
        })));
        
        $helper = $this->getScriptLocation() === 'inline'
                ? new \Zend_View_Helper_InlineScript()
                : new \Zend_View_Helper_HeadScript();
        
        $helper->appendScript(sprintf(<<<EOF
(function() {
    var elementName = '%s';
    var maximumFileSize = %s;
    jQuery('.file-api-file[data-name="' + elementName + '"]').bind('dragover', function() {
        $(this).addClass('over');
        return false;
    }).bind('dragend', function() {
        $(this).removeClass('over');
        return false;
    }).bind('dragleave', function() {
        $(this).removeClass('over');
        return false;
    })[0].ondrop = function(event) {
        var file;
        var reader;
        $(this).removeClass('over');
        event.preventDefault();
        file = event.dataTransfer.files[0];
        reader = new FileReader();
        reader.onload = function(loadEvent) {
%s
            
            if (loadEvent.target.result.length > maximumFileSize) {
                return;
            }
            jQuery('[name="' + elementName + '"]').val(loadEvent.target.result);
        };
        return reader.readAsDataURL(file);
        return false;
    };
}());
EOF
                 , $this->getName(), $this->getMaximumFileSize(), $this->getLoadCallbackScript()));
        
        $this->addDecorator('Errors')
             ->addDecorator('Description', array('tag' => 'p', 'class' => 'description'))
             ->addDecorator('HtmlTag', array('tag' => 'dd',
                                             'id'  => array('callback' => function($decorator) {
                                                return $decorator->getElement()->getId() . "-element";
                                             })))
             ->addDecorator('Label', array('tag' => 'dt'));
    }
}