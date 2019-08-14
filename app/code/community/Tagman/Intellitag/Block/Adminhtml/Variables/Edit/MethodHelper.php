<?php
final class MethodHelper{
	
	private static $taMergedFinalArray=array();
	
	/**
	 * Define methods that should not be displayed in dropdown menu
	 */
	
	private static $laProductFilteredOutMethods = array('getReservedAttributes','getIdFieldName','getResourceName','getFormatedPrice','getCacheTags','getTierPriceCount','getStoreId','getDefaultAttributeSetId');
	private static $laCategoryFilteredOutMethods = array('getDesignAttributes','getUrlInstance','getCacheIdTags','getCacheTags','getDefaultAttributeSetId','getLayoutUpdateHandle','getIdFieldName','getResourceName');
	private static $laCustomerFilteredOutMethods = array('getSharedStoreIds','getIdFieldName','getResourceName','getFieldName','getEntityTypeId','getRandomConfirmationKey','getStoreId','getAttributes','getEntityType','getStore','getCacheIdTags','getCacheTags','getPrimaryBillingAddress','getDefaultBillingAddress','getPrimaryShippingAddress','getDefaultShippingAddress');
	private static $laOrderFilteredOutMethods = array('getIdFieldName','getResourceName','getCacheIdTags','getCacheTags','getBillingAddress','getShippingAddress','getPayment','getShippingCarrier','getEmailCustomerNote');	
	private static $laCartFilteredOutMethods = array('getIsVirtual','getStore','getCacheIdTags','getCacheTags','getStoreId','getSharedStoreId','getFieldName','getResourceName','getIdFieldName','getSharedStoreIds','getItemVirtualQty');
	private static $laGeneralFilteredOutMethods = array();
	
	/**
	 * Define custom methods and methods that couldn't be pulled from system
	 */
	
	private static $laProductHardcodedMethodsList=array('getName','getPrice','getGroupPrice','getMinimalPrice','getSpecialPrice','getSpecialFromDate','getSpecialToDate','getSku','getWeight','getId','getCategoryIds','getRelatedProductIds','getUpSellProductIds','getCrossSellProductIds',"getProductCategory","getMainCategory","getSub1CategoryName","getSub2CategoryName","getSub3CategoryName");
	private static $laCategoryHardcodedMethodsList=array('getName','getId');
	private static $laCustomerHardcodedMethodsList=array('getCustomerType','getId','getGender','getFirstname','getEmail','getLastname','getPrefix','getDeliveryCity','getDeliveryCountry','getDeliveryRegion','getDeliveryPostcode');
	private static $laOrderHardcodedMethodsList=array('getOrderCurrencyCode','getTotalQtyOrdered','getTaxAmount','getRealOrderId','getId',"getCouponCode","getSubtotalInclTax","getShippingAmount","getShippingDescription","getPaymentMethod","getBaseDiscountAmount","getSubtotal");	
	private static $laCartHardcodedMethodsList=array("getCouponCode");
	private static $laGeneralHardcodedMethodsList=array("getCurrencyCode","getPageName","getPageURL","getSiteLanguage","getSiteCountryCode","getSiteCountryName","getSearchResults","getInternalSearch");
	
	/**
	 * Define second level methods (they apply to all items in cart or order)
	 */
	
	private static $laOrderSecondLavelMethodList=array("getAllItems&&getVariant","getAllItems&&getSubtotal","getAllItems&&getSimpleQtyToShip","getAllItems&&getQuantityTotal",'getAllItems&&getProductId','getAllItems&&getName','getAllItems&&getSku','getAllItems&&getSimpleQtyToShip','getAllItems&&getPrice','getAllItems&&getProductCategory',"getAllItems&&getMainCategory","getAllItems&&getSub1CategoryName","getAllItems&&getSub2CategoryName","getAllItems&&getSub3CategoryName");
	private static $laCartSecondLavelMethodList=array("getAllItems&&getVariant",'getAllItems&&getProductId','getAllItems&&getName','getAllItems&&getSku','getAllItems&&getQty','getAllItems&&getPrice','getAllItems&&getProductCategory',"getAllItems&&getMainCategory","getAllItems&&getSub1CategoryName","getAllItems&&getSub2CategoryName","getAllItems&&getSub3CategoryName");
	
	/**
	 * Prepares the array, merging dynamic and custom methods list
	 * Removes filtered dynamic variables
	 * @param array of methods $inputArray
	 * @param Magento $model
	 * @return array:
	 */
	
	public static function prepareArray($inputArray,$model){

		$addUserAttributes = false;
		$addSecondLevelUserAttribute = false;
		
		self::$taMergedFinalArray=null;
		
		switch ($model){
			case 'catalog/product':
				$filterOutArray = self::$laProductFilteredOutMethods;
				$customMethodArray = self::$laProductHardcodedMethodsList;	
				$addUserAttributes = true;			
				break;
			case 'catalog/category':
				$filterOutArray = self::$laCategoryFilteredOutMethods;
				$customMethodArray = self::$laCategoryHardcodedMethodsList;
				break;
			case 'customer/customer':
				$filterOutArray = self::$laCustomerFilteredOutMethods;
				$customMethodArray = self::$laCustomerHardcodedMethodsList;
				break;
			case 'sales/order':
				$filterOutArray = self::$laOrderFilteredOutMethods;
				$customMethodArray = array_merge(self::$laOrderHardcodedMethodsList,self::$laOrderSecondLavelMethodList);
				$addUserAttributes = true;
				$addSecondLevelUserAttribute = true;
				break;
			case 'sales/quote':
				$filterOutArray = self::$laCartFilteredOutMethods;
				$customMethodArray = array_merge(self::$laCartHardcodedMethodsList,self::$laCartSecondLavelMethodList);
				$addUserAttributes = true;
				$addSecondLevelUserAttribute = true;
				break;
			case 'general':
				$filterOutArray = self::$laGeneralFilteredOutMethods;
				$customMethodArray = self::$laGeneralHardcodedMethodsList;
				break;
			default:
				$filterOutArray=array();
				$customMethodArray=array();
				break;
		}
		
		self::$taMergedFinalArray = array_diff($inputArray, $filterOutArray);
		self::$taMergedFinalArray = array_merge(self::$taMergedFinalArray,$customMethodArray);
		
		if($addUserAttributes) self::$taMergedFinalArray = array_merge(self::$taMergedFinalArray, self::getUserDefinedAttributes($addSecondLevelUserAttribute));
		
		//TEST
		$string = "INSERT INTO tagman_intellitag_variables (name,is_static,static_value,magento_value,value) VALUES ";
		foreach (self::$taMergedFinalArray as $cMethhod){
			switch($model){
				case'catalog/product':
					$tmp='product';
					break;
				case'customer/customer':
					$tmp = 'customer';
					break;
				case'catalog/category':
					$tmp = 'category';
					break;
				case'sales/quote':
					$tmp = 'cart';
					break;
				case'sales/order':
					$tmp = 'order';
					break;
				case'general':
					$tmp = 'general';
					break;
				default:
					break;
			}
			$values[] = "('{$tmp}{$cMethhod}',2,'','{$model}&&{$cMethhod}','dynamic')";
		}
		$values = implode(",", $values);
		$values.=";";
		$string.=$values;
		//echo $string;
		//TEST
		
		return self::$taMergedFinalArray;
	}
	
	/**
	 * Get the user defined attributes from the Magento system
	 * @param bool $isSecondLevel
	 * @return array:
	 */
	
	private static function getUserDefinedAttributes($isSecondLevel){
		
		if($isSecondLevel) $tsPrefix = "getAllItems&&getAttribute&&get";
		else  $tsPrefix = "getAttribute&&get";
		
		$attributeList=array();
		
		$attributes = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getAttributeCollection();
		
		foreach ($attributes as $attr){
			
			$tsAttributeCode = $attr->getAttributeCode();			
			if ($attr->getIsUserDefined()) array_push($attributeList, $tsPrefix.self::camelize($tsAttributeCode));				
			
		}
		
		return $attributeList;
	}
	
	/**
	 * Make a camelized string from a underscore string
	 * @param string $string
	 * @return string
	 */
	
	private static function camelize($string){
		
		$tsParts=explode("_", $string);
		
		foreach ($tsParts as $tsPart){
			$camelizedWords[] = ucfirst($tsPart);
		}
		
		return implode("", $camelizedWords);
	}
}