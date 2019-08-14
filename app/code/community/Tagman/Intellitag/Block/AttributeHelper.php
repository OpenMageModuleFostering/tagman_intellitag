<?php
final class AttributeHelper{
	
	/**
	 * Get the user defined attribute value
	 * @param Magento Model $tmModel
	 * @param string $tsMethod
	 * @param bool $isAllItem
	 * @return string
	 */
	
	public static function tryToGetAttributeVariable($tmModel,$tsMethod,$isAllItem){
		
		$tsReturnValue="";		
		
		try{
			if($isAllItem){
				foreach ($tmModel as $cartItem){
					$pdmodel = Mage::getModel('catalog/product')->load($cartItem->getProduct()->getId());
					$taReturnCartValues[] = self::getAttribute($pdmodel,$tsMethod);
				}				
				$tsReturnValue = implode("|", $taReturnCartValues);
			}
			else{
				$tsMethod = explode("&&", $tsMethod);
				$tsMethod = $tsMethod[2];
				$tsReturnValue = self::getAttribute($tmModel,$tsMethod);
				if(gettype($tsReturnValue)=="array"){
					$tsReturnValue = implode("#", $tsReturnValue);
				}			
			}
		}
		catch (Exception $e){
			$tsReturnValue = "Error executing {$tsMethod}";
		}
	
		return $tsReturnValue;
	}
	
	/**
	 * 
	 * @param Magento Model $tmModel
	 * @param string $tsMethod
	 * @return string or array
	 */
	
	private static function getAttribute($tmModel,$tsMethod){
		
		$tsReturnValue = "";
		
		if($tmModel->$tsMethod()){
			$tsAttributeCode = self::attributeNameToCode($tsMethod);
			$tsDynamicValue = FilterHelper::filterInput($tmModel->getAttributeText($tsAttributeCode));
			switch (gettype($tsDynamicValue)){
				case 'boolean':
					$tsDynamicValue = FilterHelper::filterInput($tmModel->$tsMethod());
					if(gettype($tsDynamicValue)=="array"){
						$taDataList = $tmModel->$tsMethod();
						$tsDynamicValue=null;
						foreach ($taDataList as $tsData){
							$tsDynamicValue[]=implode("#", $tsData);
						}
					}
					break;
					break;
				default:
					break;
			}
			if(gettype($tsDynamicValue)=="array"){
				$tsDynamicValue = implode("#", $tsDynamicValue);
			}
			$tsReturnValue=$tsDynamicValue;
		}
		
	return $tsReturnValue;
		
	}
	
	/**
	 * Convert a camelized name to a underscore code
	 * @param string $tsString
	 * @return string
	 */
	
	private static function attributeNameToCode($tsString){
		
		$taParts = preg_split("/(?<=[a-z])(?![a-z])/", $tsString, -1, PREG_SPLIT_NO_EMPTY);
		$taParts=array_reverse($taParts);
		array_pop($taParts);
		$taParts=array_reverse($taParts);
		$taParts = array_map('strtolower', $taParts);
		$tsString = implode("_", $taParts);
		
		return $tsString;
	}
}