<?php

class EncoderController extends Zend_Controller_Action {

	public function init() {
		$this->isAdmin();
		parent::init();
	}

	public function indexAction() {
		//$this->render('genform');
		
	}
	
	public function orderstatusAction() {
		$generic = new Model_Generic();

		$this->view->msg = '';
		
		if ($this->_getParam('act') == 'edit') {
			$this->view->OrderStatusID = $this->_getParam('id');

			$cond = "OrderStatusID=" . $this->_getParam('id');
			$OrderStatusRec = $generic->getData('OrderStatus',$cond);
			$this->view->Status = $OrderStatusRec[0]['Status'];
		}

		if ($this->_getParam('act') == 'delete') {
			//delete
			$cond = "OrderStatusID=" . $this->_getParam('id');
			$OrderStatusRec = $generic->getData('OrderStatus',$cond);

			if (in_array($this->_getParam('id'), array('1','2','3','4','5'))) {
				$this->view->msg = 'Status "'. $OrderStatusRec[0]['Status'] .'" cannot be deleted!';
			} else {

				$generic->db->query("delete from OrderStatus where OrderStatusID = " . $this->_getParam('id'));

				$this->view->msg = $OrderStatusRec[0]['Status'] . ' deleted!';
			}

		}

		if (isset($_POST['btnSubmit'])) {
			if ( isset($_POST['OrderStatusID']) && !empty($_POST['OrderStatusID']) ) { #edit
			
				if (in_array($_POST['OrderStatusID'], array('1','2','3','4','5'))) {
					$this->view->msg = 'Status cannot be updated!';
				} else {
					$generic->db->query("update OrderStatus set Status = ". $generic->db->quote($_POST['Status']) ." where OrderStatusID = " . $_POST['OrderStatusID']);
					$this->view->msg = $_POST['Status'] . ' updated!';
				}
				

			} else { #insert
				$generic->db->query("insert into OrderStatus(Created, Status) values('". date('Y-m-d H:i:s') ."', ". $generic->db->quote($_POST['Status']) .") ");
				$this->view->msg = 'Successfully Saved.';
			}
		}

		$list = $generic->getData('OrderStatus',null,null,null);

    	$paginator = Zend_Paginator::factory($list);
		$curPage=$this->_getParam('page',1);
		$paginator->setItemCountPerPage(10);
		$paginator->setCurrentPageNumber($curPage);
		$this->view->list = $paginator;
	}
	
	/***
	* 
	* @example http://ezecom.local.com/encoder/genform/tbl/Brands	
	***/
    public function genformAction () {
        $generic = new Model_Generic();    	
    	$table = $this->_getParam('tbl');

		/***$protected_tables = array("users","merchandisers","outlets","agents","activity_logs","users_access");

		if(in_array($table,$protected_tables) && $authsession->user_access != 1){
			$this->_redirect('/index/');
			//$this->view->disable_input = 1;
		}

		if(in_array($table,$protected_tables)){
			$this->view->disable_input = 1;
		}***/

  		//process form
		if ( $_POST ){			
  			$data = $_POST; 

			$save = $generic->insertData($table,$data);
			$this->view->saveMsg = array('success', 'Successfully Saved.');
			$this->_redirect('/encoder/genform/tbl/'.$table);
   		}	
		
		$list = $generic->getData($table,null,"ID",null);

    	$paginator = Zend_Paginator::factory($list);
		$curPage=$this->_getParam('page',1);
		$paginator->setItemCountPerPage(10);
		$paginator->setCurrentPageNumber($curPage);
		$this->view->list = $paginator;
		
		$this->view->table = $table;
				
		$generic_call = $generic->genericForm($table);
		$this->view->genform = $generic_call; 
    }

    public function gendelAction (){
    	$generic = new Model_Generic(); 
    	
    	$table = $this->_getParam('tbl'); 
    	$key_name = $this->_getParam('kn');
    	$key_value =  $this->_getParam('kv');
    	
    	if (!$table || !$key_name || !$key_value){
    		$this->_redirect('/encoder/genform/tbl/'.$table);
    	}
    	else {
    		$cond = $key_name."=".$key_value; 
    		
    		//delete item
    		$gendel = $generic->deleteData($table,$cond);    		
  			   	    		
    		// redirect to working table
    		$this->_redirect('/encoder/genform/tbl/'.$table);
    	}
    }    
    
    public function genupdAction () {
    	$generic = new Model_Generic();

    	$table = $this->_getParam('tbl'); 
    	$key_name = $this->_getParam('kn');
    	$key_value =  $this->_getParam('kv');    	

		$knkv = array('kn'=>$key_name,'kv'=>$key_value,'tbl'=>$table);
		$this->view->knkv = $knkv;    	
    	
		$cond = $key_name."=".$key_value;
		
    	$data = $generic->getData($table,$cond);
    	$this->view->data = $data;
		$this->view->table_properties = $generic->tableProperties($table);
		
    	// if form is submiited
		if ( $_POST ) {				
			$newdata = $_POST;	
    	   	$key = $cond;  
    	   	
    	   	// update data
    		$genupd = $generic->updateData($table,$newdata,$key);    	   	
 			$this->_redirect('/encoder/genform/tbl/'.$table);		
		}
    }
    
    public function gencustomorderAction(){	
    	$generic = new Model_Generic();      	
    	
    	$table = $this->_getParam('tbl');     	
    	$this->view->data = $generic->getCustomOrder($table);
    }
	
	private function isAdmin() {
		$this->_helper->layout()->setLayout('admin2');
		$users = new Model_User();
		$authsession = new Zend_Session_Namespace('authsession');

		if( !isset($authsession->logged_admin) ){			
			$this->_redirect('/index/login/');
		}
	}
   
}