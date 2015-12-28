<?php

//Collection of static and basic functions

class Model_Site extends Model_Generic {

	//@deprecated
	public function ListBrands() {
		return $this->getData("Brands","","Name");
	}
	
	//@deprecated
	public function ListCategories() {
		return $this->getData("Category","","Name");
	}	
	
	public function sanitize($string) {		
		$tmp = strip_tags(addslashes(trim($string)));		
		return $tmp;		
	}
	
	public function ListMenus() {
		return $this->getData("Menu","","Title");
		
	}
	
	public function ListPageTitles() {
		return $this->oldSkul("select ID, Title from Pages order by Title");
	}	
	
	
	public function navigation_menu() {
		return $this->oldSkul("select t2.* from Menu t1 join MenuItems t2 on t2.MenuID = t1.ID where t1.Title = 'navigation_menu' order by t2.CustomOrder");
	}
	
	public function footer_menu() {
		return $this->oldSkul("select t2.* from Menu t1 join MenuItems t2 on t2.MenuID = t1.ID where t1.Title = 'footer_menu' order by t2.CustomOrder");
	}
	
	public function custom_menu($Menu) {
		return $this->oldSkul("select t2.* from Menu t1 join MenuItems t2 on t2.MenuID = t1.ID where t1.Title = '$Menu' order by t2.CustomOrder");
	}
	
	public function AllMenus() {
		$tmp = $this->getData("Menu");
		
		$all_menu = array();
		foreach ($tmp as $row) {
			$all_menu[$row['CustomOrder']][$row['Title']] = $this->custom_menu($row['Title']);
		}
		
		return $all_menu;
	}
	
	public function categoryMenu() {
		$categories = new Model_Categories();
		return $categories->listCategoryTree();
	}
	

	//@todo strip of image tags	
	public function widget_blurb($Title=null) {
	
		if($Title) {
			$sql = "select t1.CharLimit, t2.* from WidgetBlurb t1 join Pages t2 on t2.ID = t1.PageID where t2.Title = '$Title' limit 1";
		} else {
			$sql = "select t1.CharLimit, t2.* from WidgetBlurb t1 join Pages t2 on t2.ID = t1.PageID limit 1";
		}
		$data = $this->oldSkul($sql);
		
		if($data) {		
			return $data[0];
		}
	}
	
	public function saveInquiry($data) {
		$data['Created'] = date('Y-m-d H:i:s'); ;
		$data['LastEdited'] = date('Y-m-d H:i:s'); 
		
		
		if(!$data['Firstname'] || !$data['Email'] || !$data['Content'] || !$data['Phone'] ) {
			return array('msg'=>'Missing Required Fields.');
		} else {
			return $this->insertData('Inquiries',$data);
		}
		
		
	}
	
	

}