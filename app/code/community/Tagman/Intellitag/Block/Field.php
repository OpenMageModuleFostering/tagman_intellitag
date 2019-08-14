<?php

class Tagman_Intellitag_Block_Field 
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract 
{
    protected $_DropDownRenderer;
    protected $_TextFieldRenderer;
 
    public function __construct() 
    {
        parent::__construct();
    }

    protected function _getDropDownRenderer()
    {
        if (!$this->_DropDownRenderer) {
            $this->_DropDownRenderer = $this->getLayout()
                ->createBlock('tagman_intellitag/select')
                ->setIsRenderToJsTemplate(true);
        }
        return $this->_DropDownRenderer;
    }

	protected function _getTextFieldRenderer()
	{
		if (!$this->_TextFieldRenderer) {
			$this->_TextFieldRenderer = $this->getLayout()
              ->createBlock('tagman_intellitag/text')
              ->setIsRenderToJsTemplate(true);            
		}
		return $this->_TextFieldRenderer;
	}

	protected function _prepareToRender() 
	{
        $this->_DropDownRenderer = null;
        $this->_TextFieldRenderer = null;

        $this->addColumn('text_field', array(
            'label' => Mage::helper('tagman_intellitag')->__('Name'),
            'renderer' => 'false'

        ));
        $this->addColumn('drop_down', array(
        'label' => Mage::helper('tagman_intellitag')->__('Value')
         ));


         // Disables "Add after" button
         $this->_addAfter = false;
         $this->_addButtonLabel = Mage::helper('tagman_intellitag')->__('Add New');
    }

    protected function _renderCellTemplate($columnName)
    {
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
 
    if ($columnName=="drop_down") {
        return $this->_getDropDownRenderer()
                ->setName($inputName)
                ->setTitle($columnName)
                ->setExtraParams('style="width:200px"')
                ->setOptions(
                    // The source model defined in system config XML!
                    $this->getElement()->getValues())
                ->toHtml();
    }
     
    return parent::_renderCellTemplate($columnName);
    }

	protected function _prepareArrayRow(Varien_Object $row) 
	{
    $row->setData(
        'option_extra_attr_' . $this->_getDropDownRenderer()->calcOptionHash(
                               $row->getData('drop_down')),
        'selected="selected"'
    );
   /*
    $row->setData(
        'option_extra_attr_' . $this->_getSeconFieldRenderer()->calcOptionHash(
                               $row->getData('second_field')),
        'selected="selected"'
    );
   */
    }
}