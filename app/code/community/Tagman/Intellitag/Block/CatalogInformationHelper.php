<?php
final class CatalogInformationHelper{
	
	/**
	 * Get path in store for a given category
	 * @param Magento Category Model $tmCatalogModel
	 * @return string
	 */
	
	public static function getPathInStore($tmCatalogModel){
		
		$tsReturnValue = "";
		$tsPathInStore = "";
		$taPathNames = array();
	
		$tsPathInStore = $tmCatalogModel->getPathInStore();
		
		if($tsPathInStore!="") {
			$taPath = explode(",", $tsPathInStore);
			$taPath = array_reverse($taPath);
		}
	
		foreach ($taPath as $tsPath){
			$taPathNames[]=$tmCatalogModel->load($tsPath)->getName();
		}
		
		$tsReturnValue = implode("/", $taPathNames);
		
		return $tsReturnValue;	
	}
	
	/**
	 * Get children category names
	 * @param Magento Category Model $tmCatalogModel
	 * @return string
	 */
	
	public static function getChildren($tmCatalogModel){
	
		$tsReturnValue = "";
		$tsChildren = "";
		$taChildrenNames = array();
		$taChildren = array();
	
		$tsChildren = $tmCatalogModel->getChildren();
	
		if($tsChildren!="") {
			$taChildren = explode(",", $tsChildren);
			$taChildren = array_reverse($taChildren);
		}
	
		foreach ($taChildren as $tsChild){
			$taChildrenNames[]=$tmCatalogModel->load($tsChild)->getName();
		}
	
		$tsReturnValue = implode("|", $taChildrenNames);
	
		return $tsReturnValue;
	}
}