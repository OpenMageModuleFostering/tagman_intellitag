<?php
require_once 'FilterHelper.php';
final class GeneralInformationHelper{
	
	/**
	 * Get general information if available
	 * @param string $tsMethod
	 * @return string
	 */
	
	public static function getGeneralValue($tsMethod){
		
		$tsReturnValue="";
		
		try{
			switch ($tsMethod){
				case 'getCurrencyCode':
					$tsReturnValue = FilterHelper::filterInput(Mage::app()->getStore()->getCurrentCurrencyCode());
					break;
				case 'getPageName':
					$tsReturnValue = FilterHelper::filterInput(Mage::app()->getLayout()->getBlock('head')->getTitle());
					break;
				case 'getPageURL':
					$tsReturnValue = FilterHelper::filterInput(Mage::helper('core/url')->getCurrentUrl());
					break;
				case 'getSiteLanguage':
					$tsReturnValue = FilterHelper::filterInput(Mage::app()->getLocale()->getLocaleCode());
					break;
				case 'getSiteCountryCode':
					$tsReturnValue = FilterHelper::filterInput(Mage::getStoreConfig('general/country/default'));
					break;
				case 'getInternalSearch':
					if(Mage::app()->getRequest()->getControllerName()=="result")
						$tsReturnValue = FilterHelper::filterInput(Mage::helper('catalogsearch')->getQuery()->getQueryText());				
					break;
				case 'getSearchResults':
					if(Mage::app()->getRequest()->getControllerName()=="result")
						$tsReturnValue = FilterHelper::filterInput(Mage::helper('catalogsearch')->getQuery()->getNumResults());
					break;
				case 'getSiteCountryName':
					$tsCountryCode = Mage::getStoreConfig('general/country/default');
					$tsReturnValue = FilterHelper::filterInput( Mage::getModel('directory/country')->loadByCode($tsCountryCode)->getName());
					break;
				default:
					break;
			}	
		}
		catch(Exception $e){
			$tsReturnValue = "Error executing {$tsMethod}";
		}
		return $tsReturnValue;
	}
}