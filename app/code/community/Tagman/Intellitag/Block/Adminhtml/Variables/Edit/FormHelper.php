<?php
/**
 * Methods used to populate the Form dropdown options
 */
final class FormHelper{
	
	private static $paModelsForDropdown = array();
	
	private static $pbHasParameters;
	private static $pbIsBasicType;
	private static $pbIsBasicArray;
	private static $objectHasData;
	
	/**
	 * Get the defined list of Magento models
	 * @return array  
	 */
	
	public static function getModelsForDropdown(){
		
		self::$paModelsForDropdown['catalog/product']='Product Detail Page';
		self::$paModelsForDropdown['customer/customer']='Customer Information';
		self::$paModelsForDropdown['sales/order']='Order Confirmation Page';
		self::$paModelsForDropdown['sales/quote']='Cart & Checkout Page';
		self::$paModelsForDropdown['catalog/category']='Catalog Information';
		self::$paModelsForDropdown['general']='Other information';
		
		asort(self::$paModelsForDropdown);
		
		return self::$paModelsForDropdown;
	}
	
	/**
	 * Get 'get' class methods for given model
	 * @param Magento Model $model
	 * @return array
	 */
	
	public static function getLoadedModelGetMethods($model){
		
		$paGetMethods=array();
		
		if($model){
			foreach (get_class_methods(get_class($model)) as $tsCurrentMethodName){
				if(preg_match('/get/',$tsCurrentMethodName) && substr($tsCurrentMethodName,0,1)!="_"){
					array_push($paGetMethods, $tsCurrentMethodName);
				}
			}
		}
		
		return $paGetMethods;
	}
	
	/**
	 * Get the 'get' methods of basic type (num, string, array)
	 * @param Magento Model $model
	 * @param array $methodList
	 * @return array
	 */
	
	public static function getBasicTypeMethods($model,$methodList){	
		
		$paBasicTypeMethods=array();
		
		if ($model) {
			foreach ($methodList as $method){
				if(!self::hasParameters($model, $method)){
					try {
						$tmp = gettype($model->$method());
						if(self::isBasicType($model,$method)) array_push($paBasicTypeMethods, $method);
					}
					catch (Exception $e){						
					}
				}
			}
		}
		
		return $paBasicTypeMethods;
	}
	
	/**
	 * Check does the given method require attributes
	 * @param Magento Model $model
	 * @param String $method
	 * @return bool
	 */
	
	private static function hasParameters($model,$method){
		
 		if ($model) {
 			try{
 				$toReflectionMethod = new ReflectionMethod(get_class($model),$method);
 				$toReflectionParameters = $toReflectionMethod->getParameters();
 			}
 			catch (Exception $e){
 				
 			}
 			if (count($toReflectionParameters)==0) {
 				self::$pbHasParameters=(bool)false;
 			}
			else{
				self::$pbHasParameters=(bool)true;
			}
		}
		
		return self::$pbHasParameters;
	}
	
	/**
	 * Check does the given method return a basic type
	 * @param Magento Model $model
	 * @param String $method
	 */
	
	private static function isBasicType($model,$method){	
		
		self::$pbIsBasicType = (bool)false;
		
		switch (gettype($model->$method())){
			case 'array':
				self::$pbIsBasicType =(bool)self::handleArrayType($model,$method);
				break;
			case 'string':
			case 'integer':
			case 'double':
				self::$pbIsBasicType = (bool)true;
				break;
			default:
				self::$pbIsBasicType = (bool)false;
				break;
		}
		
		return self::$pbIsBasicType;
	}
	
	/**
	 * Check is array member of basic type
	 * @param unknown $model
	 * @param unknown $method
	 * @return boolean
	 */
	
	private static function handleArrayType($model,$method){
		
		$taReturnedArray = $model->$method();
		
		if(count($taReturnedArray)>0){
			switch (gettype(reset($taReturnedArray))){
				 case 'string':
                 case 'integer':
                 case 'double':
                 case 'boolean':
                 	self::$pbIsBasicArray = (bool)true;
                 	break;
                 default:
                 	self::$pbIsBasicArray = (bool)false;
                 	break;
			}
		}
		else{
			self::$pbIsBasicArray = (bool)false;
		}
		
		return self::$pbIsBasicArray;
	}
	
	/**
	 * Format the method names for dropdown 
	 * @param array $inputArray
	 * @return array
	 */
	
	public static function formatMethodNames($inputArray){
		
		$formatedMethodName=null;
		
		foreach ($inputArray as $rawName){
			$trimedData=null;		
			$explodedData = explode("&&", $rawName);
			foreach ($explodedData as $data){
				$trimedData[] = substr($data,3,strlen($data));
			}
			$formatedMethodName[]=implode(" / ", $trimedData);
		}
		
		return $formatedMethodName;
	}
	
	/**
	 * Merge the formated and method names in to one array 
	 * @param array $arrayValues
	 * @param array $arrayKeys
	 * @return array
	 */
	
	public static function getFinalMethodList($arrayValues,$arrayKeys){
		
		$finalMethodList=array();		
		reset($arrayKeys);
		
		foreach ($arrayValues as $tsValue){
			$finalMethodList[current($arrayKeys)] = $tsValue;
			next($arrayKeys);
		}
		asort($finalMethodList);
		
		return $finalMethodList;
	}
}