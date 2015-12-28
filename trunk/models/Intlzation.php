<?php

class Model_Intlzation extends Model_Generic {

	
	public function getSetcionContent($action,$controller,$locale='en_US',$section=null) {
	
		if($section) {
			$section = " and Section = '$section' ";
		}
		
		$tmp = $this->getData("Intlzation"," Action='$action' and Controller = '$controller' and Language='$locale' $section ");
		
		$data = array();
		foreach ($tmp as $row => $val) {
			$data[$val['Section']] = $val['LangText'];
		}
		
		return $data;
	}

	

}