<?php
require_once 'FilterHelper.php';
require_once 'AttributeHelper.php';

final class OrderAndCartInformationHelper{
	
	/**
	 * Get the needed values from all Order or Cart items
	 * @param array $taParts
	 * @param Magento Category Model $lmCategoryModel
	 * @param Magento Quote $lmVisibleItems
	 * @return string
	 */
	
	public static function handleAllItemCase($taParts,$lmCategoryModel,$lmVisibleItems){
		
		$tsReturnValue="";
		$tsMethod = $taParts[2];

		switch ($tsMethod){
			case'getAttribute':
				$tsReturnValue = AttributeHelper::tryToGetAttributeVariable($lmVisibleItems, $taParts[3],true);
				break;
			case 'getMainCategory':
			case 'getSub1CategoryName':
			case 'getSub2CategoryName':
			case 'getSub3CategoryName':
			case 'getProductCategory':
				$tsReturnValue = self::getCategoryName($lmVisibleItems,$lmCategoryModel,$tsMethod);
				break;
			case'getSubtotal':
				$tsReturnValue = self::getItemSubtotal($lmVisibleItems,$tsMethod);
				break;
			case'getVariant':
				$tsReturnValue = self::getVariant($lmVisibleItems,$tsMethod,$taParts);
				break;
			default:
				$tsReturnValue = self::getDefaultCartItemData($lmVisibleItems,$tsMethod);
				break;
		}
	
		return $tsReturnValue;
	}
	
	/**
	 * Get Item data with default methods
	 * @param Quote $lmVisibleItems
	 * @param string $tsMethod
	 * @return string
	 */
	
	private static function getDefaultCartItemData($lmVisibleItems,$tsMethod){
		
		$tsReturnValue="";
		$taCartValues = array();
		try {
			foreach ($lmVisibleItems as $tsCartItem){				
				$taCartValues[] = FilterHelper::filterInput($tsCartItem->$tsMethod());				
			}
			$tsReturnValue = join("|", $taCartValues);
		}
		catch (Exception $e){
			$tsReturnValue = "Error executing {$tsMethod}";
		}
		return $tsReturnValue;
	}
	
	/**
	 * Get the payment method name
	 * @param Magento Order Model $tmOrderModel
	 * @return string
	 */
	
	public static function getPaymentMethod($tmOrderModel){
		
		$tsReturnValue ="";
		
		if($tmOrderModel->getPayment()){
			try{
				$tsReturnValue = FilterHelper::filterInput($tmOrderModel->getPayment()->getMethodInstance()->getCode());
			}
			catch (Exception $e){
				$tsReturnValue = "Error executing getPaymentMethod";
			}
			if(gettype($tsReturnValue) == "array"){
				$tsReturnValue = implode("#", $tsReturnValue);
			}
			
		}
		return $tsReturnValue;
	}
	
	/**
	 * Get the main and sub category names
	 * @param Quote $lmVisibleItems
	 * @param Magento Category Model $tmCategoryModel
	 * @param string $tsVariableGetMethod
	 * @return string
	 */
	
	private static function getCategoryName($lmVisibleItems,$tmCategoryModel,$tsVariableGetMethod){
		
		$tsReturnValue="";
		$taCartValues = array();
		try {
			foreach ($lmVisibleItems as $tsCartItem){
				$tsDynamicValue="";
				
				$taProductCategoryId = $tsCartItem->getProduct()->getCategoryIds();
				$tsProductCategoryId =end($taProductCategoryId);
				
				$tsCategoryIds = Mage::getModel('catalog/category')->load($tsProductCategoryId)->getPathInStore();
	
				$taCategoryIds = explode(",", $tsCategoryIds);
				$taCategoryIds=array_reverse($taCategoryIds);
	
				switch ($tsVariableGetMethod){
					case'getMainCategory':
						if(count($taCategoryIds)>0)
							$tsDynamicValue = FilterHelper::filterInput($tmCategoryModel->load($taCategoryIds[0])->getName());
						break;
					case'getSub1CategoryName':
						if(count($taCategoryIds)>1)
							$tsDynamicValue = FilterHelper::filterInput($tmCategoryModel->load($taCategoryIds[1])->getName());
						break;
					case'getSub2CategoryName':
						if(count($taCategoryIds)>2)
							$tsDynamicValue = FilterHelper::filterInput($tmCategoryModel->load($taCategoryIds[2])->getName());
						break;
					case'getSub3CategoryName':
						if(count($taCategoryIds)>3)
							$tsDynamicValue = FilterHelper::filterInput($tmCategoryModel->load($taCategoryIds[3])->getName());
						break;
					case'getProductCategory':
						if(count($taCategoryIds)>0)
							$tsDynamicValue = FilterHelper::filterInput($tmCategoryModel->load(end($taCategoryIds))->getName());
						break;
					default:
						break;
				}
	
				$taCartValues[] = FilterHelper::filterInput($tsDynamicValue);
			}
			$tsReturnValue = join("|", $taCartValues);
		}
		catch (Exception $e){
			$tsReturnValue = "Error executing {$tsVariableGetMethod}";
		}
		return $tsReturnValue;
	}
	
	/**
	 * Get subtotal for each item in cart
	 * @param Quote $lmVisibleItems
	 * @param string $tsMethod
	 * @return string
	 */
	
	private static function getItemSubtotal($lmVisibleItems,$tsMethod){
		
		$tsReturnValue="";
		$taCartValues = array();
		try {
			foreach ($lmVisibleItems as $tsCartItem){
				$taCartValues[] = FilterHelper::filterInput(($tsCartItem->getPrice()*$tsCartItem->getSimpleQtyToShip()));
			}
			$tsReturnValue = join("|", $taCartValues);
		}
		catch (Exception $e){
			$tsReturnValue = "Error executing {$tsMethod}";
		}
		return $tsReturnValue;
		
	}
	
	/**
	 * Get the product variant (selected options)
	 * @param Quote $lmVisibleItems
	 * @param string $tsMethod
	 * @param array $taParts
	 * @return string or array
	 */
	
	private static function getVariant($lmVisibleItems,$tsMethod,$taParts){
		
		$tsReturnValue="";
		$taCartValues = array();
		$tsModel = $taParts[0];
	
		try {
			foreach ($lmVisibleItems as $tsCartItem){
				
				if($tsModel == 'sales/quote') $toOrderOptions = $tsCartItem->getProduct()->getTypeInstance(true)->getOrderOptions($tsCartItem->getProduct());				
				else $toOrderOptions = $tsCartItem->getProductOptions();
						
				if(array_key_exists('attributes_info', $toOrderOptions)){
					$taAttributesInfo = $toOrderOptions['attributes_info'];
				}
				else $taAttributesInfo = array();
		
				
				$taItemValue = array();
				foreach ($taAttributesInfo as $taOption){
					$taItemValue[] = $taOption['label'].' '.$taOption['value'];
				}
				$taCartValues[] = implode("#", $taItemValue);
			}
			$tsReturnValue = implode("|", $taCartValues);
		}
		catch (Exception $e){
			$tsReturnValue = "Error executing {$tsMethod}";
		}
		
		return $tsReturnValue;
	}	
}