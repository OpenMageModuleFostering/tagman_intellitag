<?php
class Tagman_Intellitag_Block_JsInjector
    extends Mage_Core_Block_Template
{
 
	public function addTagManJavascriptBlock()
	{

		$lClient = Mage::getStoreConfig('tab1/credentials/client_id_text_field',Mage::app()->getStore());
		$lSite = Mage::getStoreConfig('tab1/credentials/site_id_text_field',Mage::app()->getStore());
		$lHost = Mage::getStoreConfig('tab1/credentials/host_text_field',Mage::app()->getStore());
		
		$lJs = '';
	
		if(($lClient != 'CLIENT ID SAMPLE' && strlen($lClient) > 0) && $lSite != 'SITE ID SAMPLE' && ($lHost != 'CDN HOST SAMPLE' && strlen($lHost) > 0)){
		
		
			//page_type
			$lControlerName = Mage::app()->getRequest()->getControllerName();
			
			$lTmParams = $this->getTmParams();
			
			$lClient = str_replace(' ', '', $lClient);
			if ( is_numeric ( $lSite ) )
			  $lSite = (trim($lSite) == '')? 1 :$lSite;
			else
			  $lSite = 1;
			$lHost = str_replace(' ', '', $lHost);
			
			
			$lJs = '
						<script type="text/javascript">
							window.tmParam = {
							page_type: "'.$lControlerName.'"'.$lTmParams.'
						};
						</script>
					
						<script type="text/javascript">
					
						(function(d,s){
							var client = "'.$lClient.'";
							var siteId = '.$lSite.';
							//  do not edit
							var a=d.createElement(s),b=d.getElementsByTagName(s)[0];
							a.async=true;a.type="text/javascript";
							a.src="//'.$lHost.'/clients/"+client+"/"+siteId+".js";
							a.tagman="st="+(+new Date())+"&c="+client+"&sid="+siteId;
							b.parentNode.insertBefore(a,b);
							})(document,"script");
							
						</script>';
		} else {
		 $lJs = '<script type="text/javascript">//Setup TagMan extension properly</script>';
		}
			return ($lJs);
    }
	
	private function getTmParams()
	{
		$lVariables = unserialize(Mage::getStoreConfig("tab1/variables/field"));
		$lModel = Mage::getModel('tagman_intellitag/variables');
		$lTmParam = '';
		$aPrefix = ',
						';
		foreach($lVariables as $tVar){
			$aVarName = $tVar['text_field'];
			$lVarId = $tVar['drop_down'];
			$lModel->load($lVarId);
			$lIsStatic=$lModel->getData('is_static');
			if($lIsStatic==1){
				$lTmParam .= $aPrefix . $aVarName.': '. '"' . $lModel->getValue() .'"';
			} else {
				
				$aDynamic = explode('&&', $lModel->getData('magento_value'));
				$aModel = Mage::getModel($aDynamic[0]);
				switch($aDynamic[0]){
					case 'catalog/product':
						$lTmParam .= $this->getCatalogProduct($aPrefix, $aVarName, $aModel, $aDynamic);
					break;
					case 'catalog/category':
						$lTmParam .= $this->getCatalogCategory($aPrefix, $aVarName, $aModel, $aDynamic);
					break;
					case 'customer/customer':
						$lTmParam .= $this->getCustomerCustomer($aPrefix, $aVarName, $aModel, $aDynamic);
						break;
					case 'sales/order':
						$lTmParam .= $this->getSalesOrder($aPrefix, $aVarName, $aModel, $aDynamic);
						break;
					 case 'sales/quote':
							
						$lTmParam .= $this->getSalesQuote($aPrefix, $aVarName, $aModel, $aDynamic);
						break;
					default:
						
						break;
					}
			}
		}
		
		return ($lTmParam);
	}
	
	private function getCatalogProduct($aPrefix, $aVarName, $aModel, $aDynamic){
	$lParam = '';
	if(Mage::registry('current_product')){
					$lId=Mage::registry('current_product')->getId();
					$aModel->load($lId);
					$lMethodVal = $aModel->$aDynamic[1]();
					if (gettype($lMethodVal)=="object"){
						$lMethodVal = $lMethodVal->$aDynamic[2]();
					}
					if(gettype($lMethodVal)=="array"){
						$lMethodVal=implode("|", $lMethodVal);
					} 
					$lMethodVal = strip_tags($lMethodVal);
					$lParam .= $aPrefix . $aVarName.': "'.addslashes($lMethodVal).'"';

				}
		return ($lParam);
	}
	
	private function getCatalogCategory($aPrefix, $aVarName, $aModel, $aDynamic){
	$lParam = '';
	if(Mage::registry('current_category')){
						$lId=Mage::registry('current_category')->getId();
						$aModel->load($lId);
						$lMethodVal = $aModel->$aDynamic[1]();
						if (gettype($lMethodVal)=="object"){
							$lMethodVal = $lMethodVal->$aDynamic[2]();
						}
						if(gettype($lMethodVal)=="array"){
							$lMethodVal=implode("|", $lMethodVal);
						}

						$lParam .= $aPrefix . $aVarName.': "'.addslashes($lMethodVal).'"';
					}
	return ($lParam);
	}
	
	private function getCustomerCustomer($aPrefix, $aVarName, $aModel, $aDynamic){
	$lParam = '';
	if(Mage::getSingleton('customer/session')->isLoggedIn()) {
		   $id=Mage::getSingleton('customer/session')->getId();
		   $aModel->load($id);
		   $lMethodVal = $aModel->$aDynamic[1]();
		   if (gettype($lMethodVal)=="object"){
				$lMethodVal = $lMethodVal->$aDynamic[2]();
			}
		   if(gettype($lMethodVal)=="array"){
			   $lMethodVal=implode("|", $lMethodVal);
		   }
		   $lParam .= $aPrefix . $aVarName.': "'.addslashes($lMethodVal).'"';
		}
	return ($lParam);
	}
	
	private function getSalesOrder($aPrefix, $aVarName, $aModel, $aDynamic){
	$lParam = '';
	$aModel->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
					$lMethodVal = $aModel->$aDynamic[1]();
					
					if (gettype($lMethodVal)=="object"){
							$lMethodVal = $lMethodVal->$aDynamic[2]();
					}
					if($aDynamic[1]=="getAllItems"){
						$lMethodVal = $this->getAllItems($lMethodVal, $aDynamic);
						}
					elseif(gettype($lMethodVal)=="array"){
						$lMethodVal=implode("|", $lMethodVal);
					}
					$lParam .= $aPrefix . $aVarName.': "'.$lMethodVal.'"';
	return ($lParam);
	}
	
	private function getSalesQuote($aPrefix, $aVarName, $aModel, $aDynamic){
	$lParam = '';
		$lQuote = Mage::getSingleton('checkout/session')->getQuote();
		$lMethodVal = $lQuote->$aDynamic[1]();
		
		if($aDynamic[1]=="getAllItems"){
				$lMethodVal = $this->getAllItems($lMethodVal, $aDynamic);
			}
		
		elseif(gettype($lMethodVal)=="array"){
				$lMethodVal=implode("|", $lMethodVal);
			}
		$lParam .= $aPrefix . $aVarName.': "'.$lMethodVal.'"';
	return ($lParam);
	}
	
	private function getAllItems($lMethod, $aDynamic){
		$lAllItemsVal='';
		$lPipe = '';
		foreach($lMethod as $tItem) {
		if($tItem->getOriginalPrice()> 0){
			$lAllItemsVal .= $lPipe . addslashes($tItem->$aDynamic[2]());
			$lPipe = '|';
			}
		}
		return ($lAllItemsVal);
	}
}