<?php

//Collection of static and basic functions

class Model_Page extends Model_Generic {

	public function DisplayAll($limit=null,$except_self=null) {
	
		$condition = "";
		if($except_self) {
			$condition = " ID != ".$except_self;
		}
	
		$pages = $this->getData("Pages",$condition);
		
		return $pages;
	}

	public function Display($page_id) {
	
		if(is_numeric($page_id) ) {
			$page = $this->getData("Pages","ID = ".$page_id);
		}
		else {
			$page = $this->getData("Pages","URLSegment = '".$page_id."'");		
		}

		if(!$page) {
			return false;			
		}
		
		return $page[0];
	}
	
	public function Add($data=array()) {
	
		$page_data = array();
		
		foreach ($data as $key => $val) {
				$page_data[$key] = $val;
		}
		
		$page_data['URLSegment'] = (isset($page_data['URLSegment'])) ? strtolower($page_data['URLSegment']) : '' ;
		
		if(!$page_data['URLSegment'] || $page_data['URLSegment'] == '') {
			$tmp2 = $this->urlFriendly($page_data['URLSegment']);			
			$page_data['URLSegment'] = $tmp2;
		}
		
		$page_id = '';
		
		if (isset($page_data['ID'])) {
			$page_id = $page_data['ID'];
			unset($page_data['ID']);
			$this->updateData("Pages",$page_data,"ID=".$page_id);
		} else {
			$page_id = $this->insertData("Pages",$page_data);
		}
			
		return $page_id;
	}
	
	public function Delete($page_id){	
		$this->oldSkul("delete from Pages where ID = ".$page_id,false);
		
		return;
	}
	
	//set categories to page
	//currently only used in homepage
	public function setCategories($data) {
			
		$PageID = $data['PageID'];
			
		$categories = '';
		if(isset($data['Category'])) {
			foreach ($data['Category'] as $category) {
				$categories .= $category.",";
			}
		}
			
		$generic =  new Model_Generic();
		$generic->oldSkul("update Pages set Categories = '$categories' where ID = $PageID ",false);	
	}
	
	public function displayCategories($PageID=null,$limit=8) {
	
		$products = new Model_Products();
		
		if(!$PageID) {
			$page = $this->getData("Pages","PageType='Home'");
			if ($page) {
				$PageID = $page[0]['ID'];
			}else {
				return array();
			}
			
		} else {
			$page = $this->getData("Pages","ID=".$PageID);
		}
		
		if($page) {
			$page_categories = explode(",",$page[0]['Categories']);
		}
				
		$tmp = array();
		foreach ($page_categories as $category_id) {
			if(strlen($category_id) > 0)  {
				$tmp[] = $products->ListProducts(null, $category_id,null,null,$limit);
			}
		}
		
		$data = array();
		foreach ($tmp as $row) {
			foreach ($row as $k => $v) {
				//echo '<pre>'; print_r( $v); exit;
				//$this->getProductAttributes($v['ProductID']);
				$data[$v['CatName']][$k] = $v;
			}			
		}
		
		return $data;
	}
	
	
	private function getProductAttributes($product_id) {
		$generic = new Model_Generic();
		
		$product_attributes = $generic->getData('ProductAttributes','ProductID='.$product_id);
		
		$data = array();
		foreach ($product_attributes as $row => $val ) {
			//print_r( $row); 
			$data[$val['Name']] = $val;
		}
		
		return $data;
		
	}
	
	
	public function urlFriendly($string) {
		$pass1 = str_replace(' ', '-', strtolower($string) ); //remove spaces
		$pass2 = str_replace('`', '', strtolower($pass1) ); 
		$pass3 = str_replace("’", "", strtolower($pass2) ); //’
		$pass4 = str_replace("'", "", strtolower($pass3) ); //’
		$urlFriendly = str_replace("'", "", $pass4 ); 
		
		return preg_replace('/[^A-Za-z0-9\-]/', '', $urlFriendly);
		
	}


}