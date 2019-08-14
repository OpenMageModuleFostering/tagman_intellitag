<?php
class Tagman_Intellitag_Block_Select
    extends Mage_Core_Block_Html_Select 
{
    /**
     * Return output in one line
     * 
     * @return string
     */
    public function _toHtml()
    {
        return trim(preg_replace('/\s+/', ' ',parent::_toHtml()));
    }
}