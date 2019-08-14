<?php
class Tagman_Intellitag_Block_Adminhtml_Variables extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'tagman_intellitag';
        $this->_controller = 'adminhtml_variables';
        $this->_headerText = $this->__('Variables');

    }
}