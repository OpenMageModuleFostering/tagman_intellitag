<?php
class Tagman_Intellitag_Block_Adminhtml_Variables_Edit_Form extends Mage_Adminhtml_Block_Widget_Form{

    public function __construct(){
        parent::__construct();
        $this->setId('tagman_intelllitag_variables_form');
        $this->setTitle($this->__('Variable Information'));
    }

    protected function _prepareForm(){
        $lmModelFromRegistry = Mage::registry('tagman_intellitag');
     
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

        //define models for dropdown

        $laModelsForDropdownList=array('catalog/category'=>'catalog/category',
                                       'catalog/product'=>'catalog/product',
                                       'customer/customer'=>'customer/customer',
                                       'sales/order'=>'sales/order',
                                       'sales/quote'=>'sales/quote');

        $loFieldMagentoModelSelect = $loFieldSet->addField('magento_model', 'select', array(
            'name'      => 'magento_model',
            'label'     => Mage::helper('tagman_intellitag')->__('Model'),
            'title'     => Mage::helper('tagman_intellitag')->__('Magento Model'),
            'required'  => true,
            'options'   => $laModelsForDropdownList
        ));

        foreach($laModelsForDropdownList as $tsCurrentModelName){
            $taPropertiesForDropdownList=null;
            $tmCurrentlyLoadedModel = Mage::getModel($tsCurrentModelName);
            $tsPropertyIdForHTML=str_replace("/","_",$tsCurrentModelName);
            $tsPropertyIdForHTML.="_property";
            $tsPropertyNameForHTML=$tsPropertyIdForHTML;

            foreach (get_class_methods(get_class($tmCurrentlyLoadedModel)) as $tsCurrentMethodName){
                $tsFormatedMethodName=substr($tsCurrentMethodName,3,strlen($tsCurrentMethodName));
                $toReflectionMethod = new ReflectionMethod(get_class($tmCurrentlyLoadedModel),$tsCurrentMethodName);
                $toReflectionParameters = $toReflectionMethod->getParameters();

                //define dynamic methods that shouldn't be displayed
                switch($tsCurrentModelName){
                    case 'catalog/category':
                        $laFilteredOutMethods = array('getDesignAttributes','getUrlInstance','getCacheIdTags','getCacheTags');
                        break;
                    case 'customer/customer':
                        $laFilteredOutMethods = array('getEntityType','getStore','getCacheIdTags','getCacheTags','getPrimaryBillingAddress','getDefaultBillingAddress','getPrimaryShippingAddress','getDefaultShippingAddress');
                        break;
                    case 'sales/order':
                        $laFilteredOutMethods = array('getCacheIdTags','getCacheTags','getBillingAddress','getShippingAddress','getPayment','getShippingCarrier');
                        break;
                    case 'sales/quote':
                        $laFilteredOutMethods = array('getStore','getCacheIdTags','getCacheTags');
                        break;
                    case 'catalog/product':
                        $laFilteredOutMethods = array('getCacheIdTags','getCacheTags');
                        break;
                    default:
                        break;
                }
                if(preg_match('/get/',$tsCurrentMethodName) && substr($tsCurrentMethodName,0,1)!="_" && count($toReflectionParameters)==0 && !in_array($tsCurrentMethodName,$laFilteredOutMethods)){
                    try{
                        $property_type = gettype($tmCurrentlyLoadedModel->$tsCurrentMethodName());
                    }
                    catch(Exception $e){
                        $property_type="NULL";
                    }
                    switch($property_type){
                        case 'object':
                            $loObject=$tmCurrentlyLoadedModel->$tsCurrentMethodName();
                            $loObjectVariables=get_object_vars($loObject);
                            if(count($loObjectVariables)>0){
                                $taReturnedDataArray=$loObject->getData();
                                $taKeysList=array_keys($taReturnedDataArray);
                                foreach($taKeysList as $tsKey){
                                    $tsMethodNameExtension=str_replace("_"," ",$tsKey);
                                    $tsMethodNameExtension=ucwords($tsMethodNameExtension);
                                    $tsMethodNameExtension=str_replace(" ","",$tsMethodNameExtension);
                                    $tsPropertyDropdownValue = $tsCurrentMethodName."&&get".$tsMethodNameExtension;
                                    $taPropertiesForDropdownList[$tsPropertyDropdownValue]=$tsFormatedMethodName.' / '.$tsMethodNameExtension;
                                }
                            }
                            break;
                        case 'array':
                            $taReturnedArray = $tmCurrentlyLoadedModel->$tsCurrentMethodName();
                            if(count($taReturnedArray)>0){
                                switch($taReturnedArray[0]){
                                    case 'string':
                                    case 'integer':
                                    case 'double':
                                    case 'boolean':
                                        $taPropertiesForDropdownList[$tsCurrentMethodName]=$tsFormatedMethodName;
                                        break;
                                    case 'NULL':
                                    case 'array':
                                        break;
                                    case 'object':
                                        $loObjectArray=$tmCurrentlyLoadedModel->$tsCurrentMethodName();
                                        foreach($loObjectArray as $loObject){
                                            $loObjectVariables=get_object_vars($loObject);
                                            if(count($loObjectVariables)>0){
                                                $taReturnedDataArray = $loObject->getData();
                                                $taKeysList=array_keys($taReturnedDataArray);
                                                foreach($taKeysList as $tsKey){
                                                    $tsMethodNameExtension = str_replace("_"," ",$tsKey);
                                                    $tsMethodNameExtension = ucwords($tsMethodNameExtension);
                                                    $tsMethodNameExtension = str_replace(" ","",$tsMethodNameExtension);
                                                    $tsPropertyDropdownValue = $tsCurrentMethodName."&&get".$tsMethodNameExtension;
                                                    $taPropertiesForDropdownList[$tsPropertyDropdownValue]=$tsFormatedMethodName.' / '.$tsMethodNameExtension;
                                                }
                                            }
                                        }
                                    default:
                                        break;
                                }
                            }
                            break;
                        case 'string':
                        case 'integer':
                        case 'boolean':
                        case 'double':
                            $taPropertiesForDropdownList[$tsCurrentMethodName]=$tsFormatedMethodName;
                            break;
                        case 'NULL':
                            break;
                        default:
                            $taPropertiesForDropdownList[$tsCurrentMethodName]=$tsFormatedMethodName.'('.$property_type.') - NOT HANDELD';
                            break;
                    }
                }

                // add options manualy for models/methodes that are not initiated in backend

                switch($tsCurrentModelName){
                    case 'catalog/category':
                        $taHardcodedMethodsList=array('getName','getId','getStoreIds');
                        break;
                    case 'catalog/product':
                        $taHardcodedMethodsList=array('getName','getPrice','getGroupPrice','getCalculatedFinalPrice','getMinimalPrice','getSpecialPrice','getSpecialFromDate','getSpecialToDate','getSku','getWeight','getId','getCategoryIds','getStoreIds','getRelatedProductIds','getUpSellProductIds','getCrossSellProductIds','getPrimaryAddressIds');
                        break;
                    case 'sales/order':
                        $taHardcodedMethodsList=array('getRealOrderId','getStoreCurrency','getId','getTrackingNumbers');
                        if($tsCurrentMethodName=="getAllItems"){
                            $laObjectPropertyStrings = array('getProductId','getName','getSku','getQty','getPrice');
                            foreach($laObjectPropertyStrings as $tsObjectProperty){
                                $tsNameExtension=substr($tsObjectProperty,3);
                                $tsPropertyDropdownValue = $tsCurrentMethodName."&&".$tsObjectProperty;
                                $taPropertiesForDropdownList[$tsPropertyDropdownValue]=$tsFormatedMethodName.' / '.$tsNameExtension;
                                next($laObjectPropertyStrings);
                            }
                        }
                        break;
                    case 'customer/customer':
                        $taHardcodedMethodsList=array('getId','getPrimaryAddressIds');
                        break;
                    case 'sales/quote':
                        $taHardcodedMethodsList=array('getId');
                        if($tsCurrentMethodName=="getAllItems"){
                            $laObjectPropertyStrings = array('getProductId','getName','getSku','getQty','getPrice');
                            foreach($laObjectPropertyStrings as $tsObjectProperty){
                                $tsNameExtension=substr($tsObjectProperty,3);
                                $tsPropertyDropdownValue = $tsCurrentMethodName."&&".$tsObjectProperty;
                                $taPropertiesForDropdownList[$tsPropertyDropdownValue]=$tsFormatedMethodName.' / '.$tsNameExtension;
                                next($laObjectPropertyStrings);
                            }
                        }
                        break;
                    default:
                        $taHardcodedMethodsList=array('');
                        break;
                }
                if (in_array($tsCurrentMethodName, $taHardcodedMethodsList)) {
                    $taPropertiesForDropdownList[$tsCurrentMethodName]=$tsFormatedMethodName;
                }
            }
            $laFieldModelProperty[] = $loFieldSet->addField($tsPropertyIdForHTML, 'select', array(
                'name'      => $tsPropertyNameForHTML,
                'label'     => Mage::helper('tagman_intellitag')->__('Property'),
                'title'     => Mage::helper('tagman_intellitag')->__($tsPropertyIdForHTML),
                'required'  => true,
                'options'   => $taPropertiesForDropdownList

            ));
        }
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
		$loForm->setValues($lmModelFromRegistry->getData());
        $loForm->setUseContainer(true);
        $this->setForm($loForm);
		
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
            $modelName[]=$tsCurrentModelName;
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