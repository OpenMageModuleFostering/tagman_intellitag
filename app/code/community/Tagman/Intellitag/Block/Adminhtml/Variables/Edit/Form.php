<?php

/**
* Creates a Edit/New Form for Creating TagMan static and dynamic variables 
*/

require_once "FormHelper.php";
require_once "MethodHelper.php";

class Tagman_Intellitag_Block_Adminhtml_Variables_Edit_Form extends Mage_Adminhtml_Block_Widget_Form{
	
    public function __construct(){
        parent::__construct();
        $this->setId('tagman_intelllitag_variables_form');
        $this->setTitle($this->__('Variable Information'));
    }
    protected function _prepareForm(){
    	
    	/**
    	 * Load a TagMan variables model
    	 */
 
		$lmModelFromRegistry = Mage::registry('tagman_intellitag');
        
     	/**
     	 * Create Form field-sets and fields
     	 */
       
        
        $loForm = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post'
        ));
        $loFieldSet = $loForm->addFieldset('information_fieldset', array(
            'legend'    => Mage::helper('tagman_intellitag')->__('Variable Information'),
            'class'     => 'fieldset-wide',
        ));
        if ($lmModelFromRegistry->getId()){
            $loFieldSet->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }
        $loFieldSet->addField('value', 'hidden', array(
            'name'      => 'value'
        ));
        $loFieldSet->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => Mage::helper('tagman_intellitag')->__('Name'),
            'title'     => Mage::helper('tagman_intellitag')->__('Name'),
            'required'  => true
        ));
		$loFieldVariableType = $loFieldSet->addField('is_static', 'select', array(
            'name'      => 'is_static',
            'label'     => Mage::helper('tagman_intellitag')->__('Type'),
            'title'     => Mage::helper('tagman_intellitag')->__('is_static'),
			'options'   => array('1' => 'Static Variable','2' => 'Magento Variable'),
			'required'  => true
        ));
		$loFieldMagentoValue = $loFieldSet->addField('magento_value', 'hidden', array(
            'name'      => 'magento_value',
            'label'     => Mage::helper('tagman_intellitag')->__('Value'),
            'title'     => Mage::helper('tagman_intellitag')->__('Magento Varaible')
        ));
		$loFieldStaticValue = $loFieldSet->addField('static_value', 'text', array(
            'name'      => 'static_value',
            'label'     => Mage::helper('tagman_intellitag')->__('Value'),
            'title'     => Mage::helper('tagman_intellitag')->__('Static Variable'),
            'required'  => true
        ));
		
		/**
		 * Get Magento models and custom defined groups
		 * Used the create dropdown options in form
		 */
		
        $laModelsForDropdownList=FormHelper::getModelsForDropdown();

        $loFieldMagentoModelSelect = $loFieldSet->addField('magento_model', 'select', array(
            'name'      => 'magento_model',
            'label'     => Mage::helper('tagman_intellitag')->__('Scope'),
            'title'     => Mage::helper('tagman_intellitag')->__('Magento Model'),
            'required'  => true,
            'options'   => $laModelsForDropdownList
        ));
		
        /**
         * Loop trough every model from dropdown
         * Create dropdowns with model 'get' methods of basic type(num, string, array) 
         * Filter out 'get' methods that should not be used
         * Extend the dropdowns with custom defined methods
         */
        
        foreach($laModelsForDropdownList as $tsCurrentModelName){
        
            $taPropertiesForDropdownList=null;
         
            $tsPropertyIdForHTML=str_replace("/","_",array_search($tsCurrentModelName, $laModelsForDropdownList));
            $tsPropertyIdForHTML.="_property";
            $tsPropertyNameForHTML=$tsPropertyIdForHTML; 

			$tmCurrentlyLoadedModel = Mage::getModel(array_search($tsCurrentModelName, $laModelsForDropdownList));
			$taLoadedModelGetMethods = FormHelper::getLoadedModelGetMethods($tmCurrentlyLoadedModel);
			$taBasicTypeMethods = FormHelper::getBasicTypeMethods($tmCurrentlyLoadedModel, $taLoadedModelGetMethods);
			$taFilteredAndCustomMethods = MethodHelper::prepareArray($taBasicTypeMethods,array_search($tsCurrentModelName, $laModelsForDropdownList));
			
			$laFormatedMethodNames = FormHelper::formatMethodNames($taFilteredAndCustomMethods);
			$finalMethodList = FormHelper::getFinalMethodList($laFormatedMethodNames, $taFilteredAndCustomMethods);

            $laFieldModelProperty[] = $loFieldSet->addField($tsPropertyIdForHTML, 'select', array(
                'name'      => $tsPropertyNameForHTML,
                'label'     => Mage::helper('tagman_intellitag')->__('Value'),
                'title'     => Mage::helper('tagman_intellitag')->__($tsPropertyIdForHTML),
                'required'  => true,
                'options'   => $finalMethodList

            ));
        }
		
        /**
         * In edit mode populate the form with the saved data
         */
        
        if($lmModelFromRegistry->getData('is_static')==2){
            $taData=explode("&&",$lmModelFromRegistry->getData('magento_value'));
            $tsFieldName=str_replace("/","_",$taData[0]);
            $tsFieldName.="_property";
            $lmModelFromRegistry->setData('magento_model',$taData[0]);
            if(isset($taData[2])){
                $lmModelFromRegistry->setData($tsFieldName,$taData[1].'&&'.$taData[2]);
            }
            else{
                $lmModelFromRegistry->setData($tsFieldName,$taData[1]);
            }
        }
		if($lmModelFromRegistry->getData('is_static')==3){
			 $lmModelFromRegistry->setData("custom_value",$lmModelFromRegistry->getData('magento_value'));
		}
		$loForm->setValues($lmModelFromRegistry->getData());
        $loForm->setUseContainer(true);
        $this->setForm($loForm);
		
        /**
         * Set form field dependencies
         */
        
		$this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence','dependence_fields')
		->addFieldMap($loFieldVariableType->getHtmlId(), $loFieldVariableType->getName())
		->addFieldMap($loFieldMagentoValue->getHtmlId(), $loFieldMagentoValue->getName())
		->addFieldMap($loFieldStaticValue->getHtmlId(), $loFieldStaticValue->getName())
        ->addFieldMap($loFieldMagentoModelSelect->getHtmlId(), $loFieldMagentoModelSelect->getName())
        ->addFieldDependence(
			$loFieldStaticValue->getName(),
			$loFieldVariableType->getName(),
			'1')
		->addFieldDependence(
		    $loFieldMagentoModelSelect->getName(),
		   	$loFieldVariableType->getName(),
			'2'));
        foreach($laModelsForDropdownList as $tsCurrentModelName){
            $modelName[]=array_search($tsCurrentModelName, $laModelsForDropdownList);
        }
        $block = $this->getChild('form_after');
        for($i=0;$i<count($laFieldModelProperty);$i++){
            $block->addFieldMap($laFieldModelProperty[$i]->getHtmlId(), $laFieldModelProperty[$i]->getName());
            $block->addFieldDependence($laFieldModelProperty[$i]->getName(),$loFieldMagentoModelSelect->getName(),$modelName[$i]);
            $block->addFieldDependence($laFieldModelProperty[$i]->getName(),$loFieldVariableType->getName(),'2');
        }
        return parent::_prepareForm();
    }
}