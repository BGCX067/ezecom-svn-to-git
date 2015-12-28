<?php

class PageController extends Zend_Controller_Action {

	public function init() {
		$site = new Model_Site();
		$products = new Model_Products();
		
		$all_menus = $site->AllMenus();
		foreach($all_menus as $menu_id => $menu ) {
			$menu_key = 'menu_'.$menu_id;
			$this->view->$menu_key = $menu;
		}
		
		$this->view->widget_blurb = $site->widget_blurb(); //@todo to be removed soon
		$this->view->pagetypes = $this->pageTypes();
		
		//echo '<pre>'; print_r($products->bestSeller()); exit;
		$this->view->best_sellers = $products->bestSeller();
		$this->view->featured_products = $products->featuredProducts();
	}

	public function indexAction() {
	
		$this->_helper->layout()->setLayout('redesign-2014');
	
		$Page = new Model_Page();
		
		if($this->_getParam('pid') == 'index') {
			//$this->_redirect('/');
		}
				
		$pagedata = $Page->Display($this->_getParam('pid') );
		
		if(!$pagedata) {
			//check if product url
			$products = new Model_Products();
			$product = $products->ListProductByURL($this->_getParam('pid'));
			
			if($product) {
				$this->_helper->layout()->setLayout('redesign-2014-product');
	
				$product_id = $product['ProductID'];
				$this->view->data = $product;
				$this->view->images = $products->DisplayProductImages($product_id);
				
				$this->view->pagetype = "product";
				$this->view->product_page = 1;
				
				$this->view->site_title = $product['Name'];
				
				
				
				$this->view->product_categories = $products->DisplayProductCategories($product_id);
				$this->view->product_attributes = $products->DisplayProductAttributes($product_id);
				$this->render('product'); 		
				} 
			else {
				throw new Zend_Controller_Action_Exception('This page does not exist', 404);				
			}
		} 
		else {
			$this->view->pagetype = "page";
			$this->view->data = $pagedata;		
		}
		
		if ($pagedata['PageType'] == 'About') {
			$site = new Model_Site();
			$this->view->blurb_1 = $site->widget_blurb("Who We Are") ;
			$this->view->blurb_2 = $site->widget_blurb("Our Mission") ; 
			$this->render('about');
		}
		
		if ($pagedata['PageType'] == 'Contact') {
			$this->render('contact');
		}		
	}
	
	public function listAction() {
		
		$isAdmin = $this->isAdmin();
		
		$Page = new Model_Page();	
		$Pages = $Page->DisplayAll();
		
		$paginator = Zend_Paginator::factory($Pages);
		$curPage=$this->_getParam('page',1);
		$paginator->setItemCountPerPage(10);
		$paginator->setCurrentPageNumber($curPage);
		$this->view->list = $paginator;		
	
	}
	
	public function editAction() {
	
		$isAdmin = $this->isAdmin();
		
		$Page = new Model_Page();	
		$data = $Page->Display($this->_getParam('pid') );

		$this->view->data = $data;			
		$this->view->parentpages = $Page->DisplayAll(null,$this->_getParam('pid'));
		
		if($data['PageType'] == 'Home') {
			$categories = $Page->getData('Category');
			$this->view->categories = $categories;
		}		
		$this->render('create');
	}
	
	public function setcategoriesAction() {
		
		$this->isAdmin();

		if($_POST) {
			$Page = new Model_Page();
			$Page->setCategories($_POST);
			$PageID = $_POST['PageID'];
			$this->_redirect('/page/edit/pid/'.$PageID);
		}
		$this->_redirect('/page/list');
	}
	
	public function createAction() {
	
		$isAdmin = $this->isAdmin();
		
		$Page = new Model_Page();
		$this->view->parentpages = $Page->DisplayAll(null,$this->_getParam('pid'));
		
		if($_POST) {
			$Page = new Model_Page();
			//@todo add some validation
			
			$new_page = $Page->Add($_POST);
			
			$this->_redirect('/page/edit/pid/'.$new_page);	
		}
	}
	
	public function deleteAction() {
	
		$isAdmin = $this->isAdmin();
		
		$Page = new Model_Page();	
		$data = $Page->Delete($this->_getParam('pid') );
		
		$this->_redirect('/page/list');
	}	
		
	public function menuAction() {
		
		$isAdmin = $this->isAdmin();
		
		$generic = new Model_Generic();
		
		$this->view->menus = $generic->getData("Menu");
		
		$menu_id = isset($_GET['mid']) ? $_GET['mid'] : 0;
		$this->view->menu_id = $menu_id;
		
		
		$menu_items = $generic->getData("MenuItems", "MenuID=".$menu_id, "CustomOrder" );
		
		$this->view->menu_items = $menu_items;
		
		if($_POST) {
			
			if(isset($_POST['MenuEdit'])) {
				
				$menu_id = $_POST['MenuID'];
				
				$data = array();
				foreach($_POST['Name'] as $row => $val) {
					$data[$row]['MenuID'] = $menu_id;
					$data[$row]['Name'] = $val;
				}
				
				foreach($_POST['Link'] as $row => $val) {
					$data[$row]['Link'] = $val;
				}
				
				foreach($_POST['CustomOrder'] as $row => $val) {
					$data[$row]['CustomOrder'] = $val;
				}

				$delete = $generic->oldSkul("delete from MenuItems where MenuID = ".$menu_id,false);	
				foreach($data as $updatemenu) {
					$generic->insertData("MenuItems",$updatemenu);
				}
				$this->_redirect('/page/menu/?mid='.$_POST['MenuID']);
			}
		
			if(isset($_POST['MenuAdd'])) {
				unset($_POST['MenuAdd']);
				$save = $generic->insertData("MenuItems",$_POST);			
				if($save) {
					$this->_redirect('/page/menu/?mid='.$_POST['MenuID']);
				}
			}
		}
		
		if(isset($_GET['mitid'])) {
			$generic->oldSkul("delete from MenuItems where ID = ".$_GET['mitid'],false);
			$this->_redirect('/page/menu/?mid='.$menu_id);
		}
	}
	
	private function isAdmin() {
	
		$this->_helper->layout()->setLayout('admin2');
	
		$users = new Model_User();		
		$authsession = new Zend_Session_Namespace('authsession');

		if( !isset($authsession->logged_admin) ){			
			$this->_redirect('/index/login/');
		} 
	
	}
	
	private function pageTypes() {
		return array('Page','Home','About','Contact','TNC','Gallery');
	}
	
	

}