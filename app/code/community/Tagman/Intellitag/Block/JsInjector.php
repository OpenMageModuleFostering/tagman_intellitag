<?php
class Tagman_Intellitag_Block_JsInjector
    extends Mage_Core_Block_Template
{
 
	public function addTagManJavascriptBlock()
	{

	$client = Mage::getStoreConfig('tab1/credentials/client_id_text_field',Mage::app()->getStore());
	$site = Mage::getStoreConfig('tab1/credentials/site_id_text_field',Mage::app()->getStore());
	$host = Mage::getStoreConfig('tab1/credentials/host_text_field',Mage::app()->getStore());
	
	//page_type
	$controlerName = Mage::app()->getRequest()->getControllerName();
	
	$tmParams = $this->getTmParams();
	
	
	$js = '
				<script type="text/javascript">
					window.tmParam = {
					page_type: "'.$controlerName.'"'.$tmParams.'
				};
				</script>
			
				<script type="text/javascript">
			
				(function(d,s){
					var client = "'.$client.'";
					var siteId = '.$site.';
					//  do not edit
					var a=d.createElement(s),b=d.getElementsByTagName(s)[0];
					a.async=true;a.type="text/javascript";
					a.src="//'.$host.'/clients/"+client+"/"+siteId+".js";
					a.tagman="st=(" + new Date() +")&c=" + client + "&sid=" + siteId;
					b.parentNode.insertBefore(a,b);
					})(document,"script");
					
				</script>';
				 
        return ($js);
    }
	
	private function getTmParams()
	{
		$variables = unserialize(Mage::getStoreConfig("tab1/variables/field"));
		$cModel = Mage::getModel('tagman_intellitag/variables');
		$tmParam = '';
		$prefix = ',
						';
		foreach($variables as $var){
			$name = $var['text_field'];
			$varId = $var['drop_down'];
			$cModel->load($varId);
			$isStatic=$cModel->getData('is_static');
			if($isStatic==1){
				$tmParam .= $prefix . $name.': '. '"' . $cModel->getValue() .'"';
			} else {
				$dynamic = explode('&&', $cModel->getData('magento_value'));
				$tmp_model = Mage::getModel($dynamic[0]);
				
			switch($dynamic[0]){
				case 'catalog/product':
					$tmParam .= $this->getCatalogProduct($prefix, $name, $tmp_model, $dynamic);
				break;
				case 'catalog/category':
					$tmParam .= $this->getCatalogCategory($prefix, $name, $tmp_model, $dynamic);
				break;
				case 'customer/customer':
					$tmParam .= $this->getCustomerCustomer($prefix, $name, $tmp_model, $dynamic);
					break;
				case 'sales/order':
					$tmParam .= $this->getSalesOrder($prefix, $name, $tmp_model, $dynamic);
					break;
				 case 'sales/quote':
					$tmParam .= $this->getSalesQuote($prefix, $name, $tmp_model, $dynamic);
					break;
				default:
					
					break;
				}
			}
		}
		return ($tmParam);
	}
	
	private function getCatalogProduct($prefix, $name, $tmp_model, $dynamic){
	$lParam = '';
	if(Mage::registry('current_product')){
					$id=Mage::registry('current_product')->getId();
					$tmp_model->load($id);
					$methodVal = $tmp_model->$dynamic[1]();
					
					if (gettype($methodVal)=="object"){

						$methodVal = $methodVal->$dynamic[2]();
					}
					if(gettype($methodVal)=="array"){
						$methodVal=implode("|", $methodVal);
					} 
					$methodVal = strip_tags($methodVal);
					$lParam .= $prefix . $name.': "'.$methodVal.'"';

				}
		return ($lParam);
	}
	
	private function getCatalogCategory($prefix, $name, $tmp_model, $dynamic){
	$lParam = '';
	if(Mage::registry('current_category')){
						$id=Mage::registry('current_category')->getId();
						$tmp_model->load($id);
						$methodVal = $tmp_model->$dynamic[1]();
						if (gettype($methodVal)=="object"){
							$methodVal = $methodVal->$dynamic[2]();
						}
						if(gettype($methodVal)=="array"){
							$methodVal=implode("|", $methodVal);
						}

						$lParam .= $prefix . $name.': "'.$methodVal.'"';
					}
	return ($lParam);
	}
	
	private function getCustomerCustomer($prefix, $name, $tmp_model, $dynamic){
	$lParam = '';
	if(Mage::getSingleton('customer/session')->isLoggedIn()) {
		   $id=Mage::getSingleton('customer/session')->getId();
		   $tmp_model->load($id);
		   $methodVal = $tmp_model->$dynamic[1]();
		   //var_dump('1'.$dynamic[1]);
		   if (gettype($methodVal)=="object"){
				//var_dump('2'.$dynamic[2]);
				$methodVal = $methodVal->$dynamic[2]();
			}
		   if(gettype($methodVal)=="array"){
			   $methodVal=implode("|", $methodVal);
		   }
		   $lParam .= $prefix . $name.': "'.$methodVal.'"';
		}
	return ($lParam);
	}
	
	private function getSalesOrder($prefix, $name, $tmp_model, $dynamic){
	$lParam = '';
	$tmp_model->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
					$methodVal = $tmp_model->$dynamic[1]();
					
					if (gettype($methodVal)=="object"){
							$methodVal = $methodVal->$dynamic[2]();
					}
					if($dynamic[1]=="getAllItems"){
						$methodVal = $this->getAllItems($methodVal, $dynamic);
						}
					elseif(gettype($methodVal)=="array"){
						$methodVal=implode("|", $methodVal);
					}
					$lParam .= $prefix . $name.': "'.$methodVal.'"';
	return ($lParam);
	}
	
	private function getSalesQuote($prefix, $name, $tmp_model, $dynamic){
	$lParam = '';
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$methodVal = $quote->$dynamic[1]();
		
		if($dynamic[1]=="getAllItems"){
				$methodVal = $this->getAllItems($methodVal, $dynamic);
			}
		
		elseif(gettype($methodVal)=="array"){
				$methodVal=implode("|", $methodVal);
			}
		$lParam .= $prefix . $name.': "'.$methodVal.'"';
	return ($lParam);
	}
	
	private function getAllItems($method, $dynamic){
		$allItemsVal='';
		$pipe = '';
		foreach($method as $item) {
		if($item->getOriginalPrice()> 0){
			$allItemsVal .= $pipe . addslashes($item->$dynamic[2]());
			$pipe = '|';
			}
		}
		return ($allItemsVal);
	}
}