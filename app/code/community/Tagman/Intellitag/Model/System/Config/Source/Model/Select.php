<?php
class Tagman_Intellitag_Model_System_Config_Source_Model_Select
{
 
 
   public function toOptionArray()
    {	
		$collection = Mage::getModel('tagman_intellitag/variables')->getCollection();
		$options = array();
		foreach ($collection as $variable) {
		 $options[] = array(
			'value' => $variable->getId(),
            'label' => $variable->getName()
		  );
		
		}
		
        return $options;
    }
}