<?php
require_once "FilterHelper.php";

final class ProductDetailPageHelper{
	
	/**
	 * Get the main and sub category names
	 * @param Magento Category Model $tmCategoryModel
	 * @param string $tsVariableGetMethod
	 * @return string
	 */
	
	public static function getCategoryName($tmCategoryModel,$tsVariableGetMethod){
		$tsReturnValue="";
		$taCategoryIds = array();
		
		try{	
			if($tmCategoryModel){		

				$tsCategoryIds = $tmCategoryModel->getPathInStore();
				$taCategoryIds = explode(",", $tsCategoryIds);
				$taCategoryIds=array_reverse($taCategoryIds);
			
				switch ($tsVariableGetMethod){
					case'getMainCategory':
					if(count($taCategoryIds)>0)
						$tsReturnValue = FilterHelper::filterInput($tmCategoryModel->load($taCategoryIds[0])->getName());
						break;
					case'getSub1CategoryName':
					if(count($taCategoryIds)>1)
						$tsReturnValue = FilterHelper::filterInput($tmCategoryModel->load($taCategoryIds[1])->getName());
						break;
					case'getSub2CategoryName':
					if(count($taCategoryIds)>2)
						$tsReturnValue = FilterHelper::filterInput($tmCategoryModel->load($taCategoryIds[2])->getName());
						break;
					case'getSub3CategoryName':
						if(count($taCategoryIds)>3)
						$tsReturnValue = FilterHelper::filterInput($tmCategoryModel->load($taCategoryIds[3])->getName());
						break;
					case'getProductCategory':
					if(count($taCategoryIds)>0)
						$tsReturnValue = FilterHelper::filterInput($tmCategoryModel->load(end($taCategoryIds))->getName());
						break;
					default:
						break;
				}
			}
		}
		catch (Exception $e){
			$tsReturnValue = "Error executing {$tsVariableGetMethod}";
		}
	
		return $tsReturnValue;
	}
	
}