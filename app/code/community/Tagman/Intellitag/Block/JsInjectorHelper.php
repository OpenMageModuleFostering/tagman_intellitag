<?php

/**
 * Helpers for dynamic variables
 */

require_once 'FilterHelper.php';
require_once 'ProductDetailPageHelper.php';
require_once 'CustomerInformationHelper.php';
require_once 'CatalogInformationHelper.php';
require_once 'AttributeHelper.php';
require_once 'OrderAndCartInformationHelper.php';
require_once 'GeneralInformationHelper.php';

final class JsInjectorHelper{
	
	private static $lmProductPageModel = null;
	private static $lmCategoryModel = null;
	private static $lmCustomerModel = null;
	private static $lmCartModel = null;
	private static $lmOrderModel = null;
	private static $lmGeneralCategoryModel = null;
	private static $lmVisibleItems = null;
	
	/**
	 * Load all needed models
	 */
	
	public static function loadModels(){
		
		if(Mage::registry('current_product')){
			
			$tsProductModelId = Mage::registry('current_product')->getId();
			self::$lmProductPageModel = Mage::getModel('catalog/product');
			self::$lmProductPageModel->load($tsProductModelId);
			
		}
		
		if(Mage::registry('current_category')){
			
			$tsCategoryModelId = Mage::registry('current_category')->getId();
			self::$lmCategoryModel = Mage::getModel('catalog/category');
			self::$lmCategoryModel->load($tsCategoryModelId);
			
		}
		
		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			
			$tsCustomerid=Mage::getSingleton('customer/session')->getId();
			self::$lmCustomerModel = Mage::getModel('customer/customer');
			self::$lmCustomerModel->load($tsCustomerid);
			
		}
		
		if(count(Mage::getSingleton('checkout/session')->getQuote()->getAllItems())>0 &&
		   preg_match('/checkout/',Mage::helper('core/url')->getCurrentUrl()) &&
		   !preg_match('/success/',Mage::helper('core/url')->getCurrentUrl())){
			self::$lmCartModel = Mage::getSingleton('checkout/session')->getQuote();
			self::$lmVisibleItems = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
		}
		
		if(preg_match('/success/',Mage::helper('core/url')->getCurrentUrl())){
			self::$lmOrderModel = Mage::getModel('sales/order');
			self::$lmOrderModel->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
			self::$lmVisibleItems = self::$lmOrderModel->getAllVisibleItems();			
		}
		self::$lmGeneralCategoryModel = Mage::getModel('catalog/category');
	}	
	
	/**
	 * Get dynamic variables from the product detail page 
	 * @param string $tmTagManDynamicValue
	 * @param string $tsMachineFriendlyName
	 * @return string
	 */
	
	public static function getProductPageVariable($tmTagManDynamicValue,$tsMachineFriendlyName){
		
		$tsReturnValue = "";

		if(self::$lmProductPageModel) {
			
			$tsParts = explode("&&", $tmTagManDynamicValue);
			$tsVariableGetMethod = $tsParts[1];
			
			switch ($tsVariableGetMethod){
				case 'getMainCategory':
				case 'getSub1CategoryName':
				case 'getSub2CategoryName':
				case 'getSub3CategoryName':
				case 'getProductCategory':
					$tsReturnValue = ProductDetailPageHelper::getCategoryName(self::$lmCategoryModel, $tsVariableGetMethod);
					break;
				case 'getAllItems':
					break;
				case 'getAttribute':
					$tsReturnValue = AttributeHelper::tryToGetAttributeVariable(self::$lmProductPageModel, $tmTagManDynamicValue,false);
					break;
				default:
					$tsReturnValue = self::tryToGetVariable(self::$lmProductPageModel, $tsVariableGetMethod);
					break;
			}
			$tsReturnValue=self::formatReturn($tsReturnValue, $tsMachineFriendlyName);
		}
		
		return 	$tsReturnValue;	
	}
	
	/**
	 * Get dynamic variables if the customer is logged in
	 * @param string $tmTagManDynamicValue
	 * @param string $tsMachineFriendlyName
	 * @return string
	 */
	
	public static function getCustomerInformationVariable($tmTagManDynamicValue,$tsMachineFriendlyName){
		
		$tsReturnValue = "";
	
		if(self::$lmCustomerModel) {
				
			$tsParts = explode("&&", $tmTagManDynamicValue);
			$tsVariableGetMethod = $tsParts[1];
				
			switch ($tsVariableGetMethod){
				case 'getDeliveryRegion':
				case 'getDeliveryPostcode':
				case 'getDeliveryCountry':
				case 'getDeliveryCity':
					$tsReturnValue = CustomerInformationHelper::getDeliveryInfo(self::$lmCustomerModel, $tsVariableGetMethod);
					break;
				case 'getGender':
					$tsReturnValue = CustomerInformationHelper::getGender(self::$lmCustomerModel,$tsVariableGetMethod);
					break;
				case 'getCustomerType':
					$tsReturnValue = CustomerInformationHelper::getCustomerType(self::$lmCustomerModel,$tsVariableGetMethod);
					break;					
				case 'getAllItems':
					break;
				default:
					$tsReturnValue = self::tryToGetVariable(self::$lmCustomerModel, $tsVariableGetMethod);
					break;
			}
			$tsReturnValue=self::formatReturn($tsReturnValue, $tsMachineFriendlyName);
		}
	
		return 	$tsReturnValue;
	}
	
	/**
	 * Get dynamic variables if on a catalog page
	 * @param string $tmTagManDynamicValue
	 * @param string $tsMachineFriendlyName
	 * @return string
	 */
	
	public static function getCatalogInformation($tmTagManDynamicValue,$tsMachineFriendlyName){
		$tsReturnValue = "";
		if(self::$lmCategoryModel) {
			
			$tsParts = explode("&&", $tmTagManDynamicValue);
			$tsVariableGetMethod = $tsParts[1];
			
			switch ($tsVariableGetMethod){
				case 'getAllItems':
					break;
				case 'getChildren':
					$tsReturnValue = CatalogInformationHelper::getChildren(self::$lmCategoryModel);
					break;
				case 'getPathInStore':
					$tsReturnValue = CatalogInformationHelper::getPathInStore(self::$lmCategoryModel);
					break;
				default:
					$tsReturnValue = self::tryToGetVariable(self::$lmCategoryModel, $tsVariableGetMethod);
					break;
			}
			$tsReturnValue=self::formatReturn($tsReturnValue, $tsMachineFriendlyName);
		}
		return 	$tsReturnValue;
	}
	
	/**
	 * Get dynamic variables from the cart and checkout page 
	 * @param string $tmTagManDynamicValue
	 * @param string $tsMachineFriendlyName
	 * @return string
	 */
	
	public static function getCartAndCheckoutInformation($tmTagManDynamicValue,$tsMachineFriendlyName){
		$tsReturnValue = "";
		if(self::$lmCartModel) {
				
			$tsParts = explode("&&", $tmTagManDynamicValue);
			$tsVariableGetMethod = $tsParts[1];
		
			switch ($tsVariableGetMethod){
				case 'getAllItems':
					$tsReturnValue = OrderAndCartInformationHelper::handleAllItemCase($tsParts,self::$lmGeneralCategoryModel,self::$lmVisibleItems);
					break;
				default:
					$tsReturnValue = self::tryToGetVariable(self::$lmCartModel, $tsVariableGetMethod);
					break;
			}
			$tsReturnValue=self::formatReturn($tsReturnValue, $tsMachineFriendlyName);
		}
		return 	$tsReturnValue;
	}
	
	/**
	 * Get dynamic variables from the order confirmation page 
	 * @param string $tmTagManDynamicValue
	 * @param string $tsMachineFriendlyName
	 * @return string
	 */
	
	public static function getOrderInformation($tmTagManDynamicValue,$tsMachineFriendlyName){
		$tsReturnValue = "";
		if(self::$lmOrderModel) {
				
			$tsParts = explode("&&", $tmTagManDynamicValue);
			$tsVariableGetMethod = $tsParts[1];
		
			switch ($tsVariableGetMethod){
				case 'getAllItems':
					$tsReturnValue = OrderAndCartInformationHelper::handleAllItemCase($tsParts,self::$lmGeneralCategoryModel,self::$lmVisibleItems);
					break;
				case'getPaymentMethod':
					$tsReturnValue = OrderAndCartInformationHelper::getPaymentMethod(self::$lmOrderModel);
					break;				
				case'getBaseDiscountAmount':
					$tsReturnValue = abs(self::tryToGetVariable(self::$lmOrderModel, $tsVariableGetMethod));
					break;
				case'getTotalQtyOrdered':
					$tsReturnValue = abs(self::tryToGetVariable(self::$lmOrderModel, $tsVariableGetMethod));
					break;
				default:
					$tsReturnValue = self::tryToGetVariable(self::$lmOrderModel, $tsVariableGetMethod);
					break;
			}
			$tsReturnValue=self::formatReturn($tsReturnValue, $tsMachineFriendlyName);
		}
		return 	$tsReturnValue;
	}
	
	/**
	 * Get dynamic variables if data is available
	 * @param string $tmTagManDynamicValue
	 * @param string $tsMachineFriendlyName
	 * @return string
	 */
	
	public static function getGeneralInformation($tmTagManDynamicValue,$tsMachineFriendlyName){
		$tsReturnValue = "";
					
			$tsParts = explode("&&", $tmTagManDynamicValue);
			$tsVariableGetMethod = $tsParts[1];
			
			switch ($tsVariableGetMethod){
				case 'getCurrencyCode':
				case 'getPageName':
				case 'getPageURL':
				case 'getSiteLanguage':
				case 'getSiteCountryCode':
				case 'getInternalSearch':
				case 'getSearchResults':
				case 'getSiteCountryName':
					$tsReturnValue = GeneralInformationHelper::getGeneralValue($tsVariableGetMethod);
					break;
				case 'getAllItems':
					break;
				default:
					$tsReturnValue = self::tryToGetVariable(self::$lmCategoryModel, $tsVariableGetMethod);
					break;
			}
			$tsReturnValue=self::formatReturn($tsReturnValue, $tsMachineFriendlyName);
		
		return 	$tsReturnValue;
	}
	
	/**
	 * Make a formated string for Javascript code injection
	 * @param string $returnInput
	 * @param string $machineFriendlyName
	 * @return string
	 */
	
	private static function formatReturn($returnInput,$machineFriendlyName){	
		$formatedReturn = "";	
		if(gettype($returnInput)=="array"){
			$returnInput = implode("|", $returnInput);
		}
		$formatedReturn = "\ntmParam['{$machineFriendlyName}'] = '{$returnInput}';";
		return $formatedReturn;
	}
	
	/**
	 * Get dynamic value with default method
	 * @param Magento Model $tmModel
	 * @param string $tsMethod
	 * @return array or string
	 */
	
	private static function tryToGetVariable($tmModel,$tsMethod){

		$tsReturnValue="";
		try{
			$tsReturnValue = FilterHelper::filterInput($tmModel->$tsMethod());
			if(gettype($tsReturnValue)=="array"){
				$tsReturnValue = implode("|", $tsReturnValue);
			}
		}
		catch (Exception $e){
			$tsReturnValue = "Error executing {$tsMethod}";
		}
		
		return $tsReturnValue;
	}
}