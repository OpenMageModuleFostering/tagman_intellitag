<?php

/**
 * Inserts the TagMan intellitag Javascript code in to the head of every page
 * The configured dynamic values are scrapped from the viewed page
 */

require_once 'JsInjectorHelper.php';
require_once 'FilterHelper.php';

class Tagman_Intellitag_Block_JsInjector extends Mage_Core_Block_Template {
	
 	private static $tbReturnValue;
 	
 	/**
 	 * Creates the TagMan Javascript code for head injection
 	 * @return string
 	 */
 	
	public function addTagManJavascriptBlock(){
		
		$psFinalCodeForInjection="<script type='text/javascript'>\n//BEGINING OF TagMan CODE\n\n";
		
		/**
		 * Get TagMan credentials from configuration page
		 * If credentials are set build the intellitag
		 */
		
		$psClientName = FilterHelper::filterInput("".Mage::getStoreConfig('tab1/credentials/client_id_text_field',Mage::app()->getStore()));
		$psSiteId = FilterHelper::filterInput("".Mage::getStoreConfig('tab1/credentials/site_id_text_field',Mage::app()->getStore()));
		$psHostURL = FilterHelper::filterInput("".Mage::getStoreConfig('tab1/credentials/host_text_field',Mage::app()->getStore()));		
		
		if(!self::isStringEmpty($psClientName) && !self::isStringEmpty($psSiteId) && !self::isStringEmpty($psHostURL)){	
					
			$psFinalCodeForInjection.=self::createTmObject();
			$psFinalCodeForInjection.=self::createIntelliTag($psClientName,$psSiteId,$psHostURL);
			$psFinalCodeForInjection.=self::insertTagManLoadVerification();
			$psFinalCodeForInjection.=self::addPageType();
			
			$laConfiguredVariables = unserialize(Mage::getStoreConfig("tab1/variables/field"));
			$lmTagManVariableModel = Mage::getModel('tagman_intellitag/variables');
			JsInjectorHelper::loadModels();
			
			/**
			 * Get configured variables 
			 */
			
			foreach($laConfiguredVariables as $tsConfiguredVariable){
				
				$tsMachineFriendlyName = $tsConfiguredVariable['text_field'];
				$tsVariableModelId = $tsConfiguredVariable['drop_down'];

				$lmTagManVariableModel->load($tsVariableModelId);

				switch ($lmTagManVariableModel->getIsStatic()){
					case '1':
						$psFinalCodeForInjection.= self::handleStaticVariable($lmTagManVariableModel->getStaticValue(),$tsMachineFriendlyName);
						break;
					case'2':
						$psFinalCodeForInjection.= self::handleDynamicVariable($lmTagManVariableModel->getMagentoValue(),$tsMachineFriendlyName);
						break;
					default:
						break;	
				}
			}	
		}
		else {
			$psFinalCodeForInjection.=self::insertAlertMsg("Fill In TagMan Client Credentials!");
		}
			    
		$psFinalCodeForInjection.="\n\n//END OF TagMan CODE\n</script>";
		
		return $psFinalCodeForInjection;
    }
    
    /**
     * Determine Magento page type and prepare the string for injection
     * @return string
     */
    
    private static function addPageType(){
    	
    	$tsPageType = FilterHelper::filterInput(Mage::app()->getRequest()->getControllerName());
    	$tsReturnValue="\ntmParam['page_type'] = '{$tsPageType}';";
    	
    	return $tsReturnValue;
    }
    
    /**
     * Get defined static variable and prepare the string for injection
     * @param string $tmTagManStaticValue
     * @param string $tsMachineFriendlyName
     * @return string
     */
    
    private static function handleStaticVariable($tmTagManStaticValue,$tsMachineFriendlyName){
    	
    	$tmTagManStaticValue = FilterHelper::filterInput($tmTagManStaticValue);
    	$tsReturnValue="\ntmParam['{$tsMachineFriendlyName}'] = '{$tmTagManStaticValue}';";
    	
    	return $tsReturnValue;
    }
    
    /**
     * Get defined dynamic variable and prepare the string for injection
     * @param unknown $tmTagManDynamicValue
     * @param unknown $tsMachineFriendlyName
     * @return string
     */
    
    private static function handleDynamicVariable($tmTagManDynamicValue,$tsMachineFriendlyName){
    	
    	$tsParts = explode("&&", $tmTagManDynamicValue);
    	
    	switch ($tsParts[0]){
    		case 'catalog/product':
    			$tsReturnValue = JsInjectorHelper::getProductPageVariable($tmTagManDynamicValue, $tsMachineFriendlyName);
    			break;
    		case 'customer/customer':
    			$tsReturnValue = JsInjectorHelper::getCustomerInformationVariable($tmTagManDynamicValue, $tsMachineFriendlyName);
    			break;
    		case 'catalog/category':
    			$tsReturnValue = JsInjectorHelper::getCatalogInformation($tmTagManDynamicValue, $tsMachineFriendlyName);
    			break;
    		case 'sales/order':
    			$tsReturnValue = JsInjectorHelper::getOrderInformation($tmTagManDynamicValue, $tsMachineFriendlyName);
    			break;
    		case 'sales/quote':
    			$tsReturnValue = JsInjectorHelper::getCartAndCheckoutInformation($tmTagManDynamicValue, $tsMachineFriendlyName);
    			break;
    		case 'general':
    			$tsReturnValue = JsInjectorHelper::getGeneralInformation($tmTagManDynamicValue, $tsMachineFriendlyName);
    			break;
    		default:
    			break;
    	}

    	return $tsReturnValue;
    }
    
    /**
     * Create empty Javascript tmParam object
     * @return string
     */
    
	private static function createTmObject(){
		
		$tsReturnValue = "window.tmParam = {};\n\n";
		
		return $tsReturnValue;
	}
	
	/**
	 * Create TagMan Intellitag Javascript code
	 * @param string $tsClientName
	 * @param string $tsSiteId
	 * @param string $tsHostURL
	 * @return string
	 */
	
	private static function createIntelliTag($tsClientName,$tsSiteId,$tsHostURL){
		
		$tsReturnValue = "(function(d,s){
							var client = '{$tsClientName}';
							var siteId = '{$tsSiteId}';
							//  do not edit
							var a=d.createElement(s),b=d.getElementsByTagName(s)[0];
							a.async=true;a.type='text/javascript';
							a.src='//{$tsHostURL}/clients/'+client+'/'+siteId+'.js';
							a.tagman='st='+(+new Date())+'&c='+client+'&sid='+siteId;
							b.parentNode.insertBefore(a,b);
							})(document,'script');";
		
		return $tsReturnValue;
	}
	
	/**
	 * Insert an alert that fires if the TagMan code did not load
	 * @return string
	 */
	
	private static function insertTagManLoadVerification(){
		
		$tsReturnValue = "\n\nsetTimeout(function(){if(!window.TAGMAN) alert('TagMan did not load. Check your credentials!');},8000);\n";
		
		return $tsReturnValue;
	}
	
	/**
	 * Insert an alert
	 * @param string $tsString
	 * @return string
	 */
	
    private static function insertAlertMsg($tsString){
    	
    	$tsString = FilterHelper::filterInput($tsString);  	
    	$tsReturnValue = "setTimeout(function(){alert('{$tsString}')},5000);";    
    		
    	return $tsReturnValue;
    }
    
    /**
     * Check is string empty
     * @param string $tsString
     * @return bool
     */
    private static function isStringEmpty($tsString){
    	    	
    	$tsString = trim($tsString);
    	if(strlen($tsString)>0) self::$tbReturnValue = (bool)false;
    	else self::$tbReturnValue = (bool)true;
    	
    	return self::$tbReturnValue;
    }
}