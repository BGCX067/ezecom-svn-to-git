<?php

//Collection of static and basic functions

class Model_Inquiries extends Model_Generic {

	public function listInquiries($id=null,$limit=null) {
		
		if($id) {
			$data = $this->getData("Inquiries","ID=".$id,"Created desc");	
			if($data && isset($data[0])){
				return $data[0];
			}	
		}else {
			$data = $this->getData("Inquiries","","Created desc");	
			return $data;		
		}
	
		
	}

}