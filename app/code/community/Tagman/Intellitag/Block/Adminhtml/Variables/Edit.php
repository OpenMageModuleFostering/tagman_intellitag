<?php
class Tagman_Intellitag_Block_Adminhtml_Variables_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'tagman_intellitag';
        $this->_controller = 'adminhtml_variables';

        $this->_updateButton('save', 'label', $this->__('Save Variable'));
        $this->_updateButton('delete', 'label', $this->__('Delete Variable'));

        parent::__construct();

    }

    public function getHeaderText()
    {
        if (Mage::registry('tagman_intellitag')->getId()) {
            return $this->__('Edit Variable');
        }
        else {
            return $this->__('New Variable');
        }
    }
}