<?php

//Collection of static and basic functions

class Model_Widgetblurb extends Model_Generic {

	function add($PageID,$Charlimit=120,$WidgetID=null) {		
	
		$Charlimit = isset($Charlimit) ? $Charlimit : 120;
		
		$data = array(
			'Created' => date('Y-m-d H:i:s'),
			'LastEdited' => date('Y-m-d H:i:s'),
			'PageID' => $PageID,
			'CharLimit' => $Charlimit,
		);
	
		if($WidgetID > 0) {
			$update = $this->oldSkul("update WidgetBlurb set PageID = $PageID, CharLimit = $Charlimit where ID= $WidgetID ",false);			
		}
		else {
			$save = $this->insertData("WidgetBlurb",$data);
		}
		
	}
	
	function listBlurbs() {
		return $this->oldSkul("select t1.ID, t1.PageID, t1.CharLimit, t2.Title from WidgetBlurb t1 join Pages t2 on t2.ID = t1.PageID");
	}

}