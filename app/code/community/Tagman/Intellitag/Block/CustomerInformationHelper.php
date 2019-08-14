<?php
require_once 'FilterHelper.php';
final class CustomerInformationHelper{
	
	/**
	 * Get the gender of a customer
	 * @param Magento Customer Model $tmCustomerModel
	 * @param string $tsMethod
	 * @return array or string
	 */
	
	public static function getGender($tmCustomerModel,$tsMethod){
		$tsReturnValue ="";
		try{
			$tsReturnValue = FilterHelper::filterInput(Mage::getResourceSingleton('customer/customer')->getAttribute('gender')->getSource()->getOptionText($tmCustomerModel->$tsMethod()));
		}
		catch (Exception $e){
			$tsReturnValue = "Error executing {$tsMethod}";
		}
		if(gettype($tsReturnValue) == "array"){
			$tsReturnValue = implode("#", $tsReturnValue);
		}
		return $tsReturnValue;
	}
	
	/**
	 * Get delivery info
	 * @param Magento Customer Model $tmCustomerModel
	 * @param string $tsMethod
	 * @return string
	 */
	
	public static function getDeliveryInfo($tmCustomerModel,$tsMethod){
		
		$tsReturnValue ="";
		$tsDynamicValue="";
		$tsGetFromAddress="";
		$isCountry = false;
		
		switch ($tsMethod){
			case 'getDeliveryCity':
				$tsGetFromAddress = "getCity";
				break;
			case 'getDeliveryRegion':
				$tsGetFromAddress = "getRegion";
				break;
			case 'getDeliveryPostcode':
				$tsGetFromAddress = "getPostcode";
				break;
			case 'getDeliveryCountry':
				$tsGetFromAddress = "getCountry";
				$isCountry = true;
			default:
				break;
		}
		
		try{
			$tsBillingId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
			if ($tsBillingId) {
				$tsReturnValue =FilterHelper::filterInput(Mage::getModel('customer/address')->load($tsBillingId)->$tsGetFromAddress());
				if($isCountry)	 $tsReturnValue = FilterHelper::filterInput(Mage::getModel('directory/country')->loadByCode($tsReturnValue)->getName());	
			}
		}
		
		catch (Exception $e){
			$tsReturnValue = "Error executing {$tsMethod}";
		}		
		if(gettype($tsReturnValue) == "array"){
			$tsReturnValue = implode("#", $tsReturnValue);
		}		
		return $tsReturnValue;
	}
	
	/**
	 * Get type of customer
	 * If customer bought something the type is returning customer
	 * Else new customer
	 * @param Magento Model $tmCustomerModel
	 * @param string $tsMethod
	 * @return string
	 */
	
	public static function getCustomerType($tmCustomerModel,$tsMethod){
		$tsReturnValue ="";
		try{
			$tiNumberOfOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_id',$tmCustomerModel->getId())->count();
			if($tiNumberOfOrders>0) $tsReturnValue = "returning customer";
			else $tsReturnValue = "new customer";
		}
		catch (Exception $e){
			$tsReturnValue = "Error executing {$tsMethod}";
		}
		if(gettype($tsReturnValue) == "array"){
			$tsReturnValue = implode("#", $tsReturnValue);
		}
		return $tsReturnValue;
	}
	
}