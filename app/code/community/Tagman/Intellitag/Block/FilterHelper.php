<?php
final class FilterHelper{
	
	/**
	 * Filter an input
	 * @param string or array $tsInput
	 * @return string or array
	 */
	
	public static function filterInput($tsInput){
		
		$tsReturnValue="";
		$taReturnedList = array();
		
		switch (gettype($tsInput)){
			case 'array':
				foreach ($tsInput as $tsValue){
					$taReturnedList[]= self::filterInputString($tsValue);
				}
				$tsReturnValue=$taReturnedList;
				break;
			case 'string':
				$tsReturnValue = self::filterInputString($tsInput);
				break;
			default:
				$tsReturnValue = $tsInput;
				break;
		}
		return $tsReturnValue;
	
	}
	
	/**
	 * Filter a string
	 * Trim, strip tags, add slashes
	 * @param string $tsString
	 * @return string
	 */
	
	private static function filterInputString($tsString){
		$tsString = trim($tsString);
		//$tsString = htmlspecialchars($tsString);
		$tsString = strip_tags($tsString);
		$tsString = addslashes($tsString);
		return  $tsString;
	}
}