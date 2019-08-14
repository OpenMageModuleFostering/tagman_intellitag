<?php
class Tagman_Intellitag_Block_Select
    extends Mage_Core_Block_Html_Select 
{

    public function _toHtml()
    {
        return trim(preg_replace('/\s+/', ' ',parent::_toHtml()));
    }
}