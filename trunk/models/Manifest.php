<?php

class Model_Manifest {

	public function Build($filename,$data=array()) {
	
		$file = CACHE_PATH.$filename.".xml";

		$xml_head = '<?xml version="1.0" standalone="yes"?>';
		$xml_head .= "\n";
		
		$xml_contents ="<records>"."\n";;
		foreach ($data as $row => $val) {		
			if(is_array($val)) {
				$xml_contents.="	<record>"."\n";	
				foreach ($val as $k => $v) {						
					$xml_contents.="		<$k>".htmlspecialchars($v)."</$k>"."\n";
				}
				$xml_contents.="	</record>"."\n";

			} else {
				$xml_contents.="	<record>"."\n";		
				$xml_contents.="		<$row>".htmlspecialchars($val)."</$row>"."\n";			
				$xml_contents.="	</record>"."\n";	
			}
		}
		$xml_contents .="</records>";

		$fp = fopen($file, 'w+');
		fwrite($fp,$xml_head.$xml_contents);
		
		return $this->loadXml($file);
	}
	
	
	private function loadXml($file) {
		
		$tmp = simplexml_load_file($file);
		
		$data = array();
		$num = 0;
		foreach($tmp->children() as $child){
			$newnum = $num++;		  
			foreach ($child->children() as $grandkids) {
				$key = $grandkids->getName() ;
				$data[$newnum][$key] = $grandkids;
			}
		}		
		return $data;	
	}
	
	public function Load($filename,$data,$forcebuild=null) {
		if(file_exists(CACHE_PATH.$filename.".xml")) {
			$manifest_data = $this->loadXml(CACHE_PATH.$filename.".xml");
		}
		else {	
			$manifest_data = $this->Build($filename,$data);	
		}		
		if(isset($_GET['build'])  || $forcebuild==1) {
			$manifest_data = $this->Build($filename,$data);	
		}

		return $manifest_data;	
	}


}