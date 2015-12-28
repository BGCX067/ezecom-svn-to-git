<?php 

class Model_User extends Model_Generic {// note the class name

	public function details($email) {
		if(strpos($email,'@') === false) {
			$details = $this->getData("SiteUsers","SiteUsername='".$email."'");
		} else {			
			$details = $this->getData("SiteUsers","SiteEmail='".$email."'");
		}
		return $details[0];
	}

	public function validate($username,$password,$ip_address=null) {
		//$ip_address = $_SERVER['REMOTE_ADDR'];
		//$allowed_ips = array('124.107.191.17','122.2.48.250','122.53.69.85');		
		
		//$generic = new Model_Generic();
		
		$username = strip_tags($username);
		$password= strip_tags($password);
		
		if(strpos($username,"@") === false) {
			$cond = "SiteUsername='".$username."'";			
		} else {			
			$cond = "SiteEmail='$username' ";
		} 
		
		$sql = "select * from SiteUsers where $cond and SitePassword='$password' ";
		
		$validate = $this->oldSkul($sql);	

		$loginsta = count($validate);
				
			if($loginsta == 1 && $validate[0]['anAdmin'] == 1) {
				$authsession = new Zend_Session_Namespace('authsession');
				$authsession->logged_admin = $username;
				return $authsession->logged_admin;			
			}		
			elseif($loginsta == 1) {
				$authsession = new Zend_Session_Namespace('authsession');
				$authsession->logged_user = $username;
				return $authsession->logged_user;	
			}
		
		
		return false;
	}
	
	public function checklogin(){	
		$authsession = new Zend_Session_Namespace('authsession');	
	
		$logininfo = '';
		if(isset($authsession->logged_user)) {
			$username = $authsession->logged_user;
			$logininfo = $username;
		}

		return $logininfo;
	}
	
	public function deleteUser($user_id){
		$delete = $this->deleteData('users'," user_id = $user_id ");		
	}
	
	public function updateUserInfo($data) {
		
		$user_id = $data['UserID'];
		unset($data['UserID']);
		
		unset($data['Password']);
		
		$cond = " set ";
		foreach ($data as $field => $value) {
			$cond .= " $field = '".$value."' ," ;
		}
		$cond .= "x"; //signify the end of the array
		
		$cond = str_replace(",x","",$cond);
		
		//echo $cond; exit;
		
		$sql = "update SiteUsers $cond where ID=".$user_id;
		//echo $sql; exit;
		$this->oldSkul($sql,false);	
	}
	
	
	
	public function registerUser($data) {
		
		$Site = new Model_Site();
		
		//check required fields
		if(!$data['Email'] || !$data['esig1234'] ||  !$data['password1'] ||  !$data['password2']   ||  !$data['Firstname'] ) {
			return array('msg'=>'Missing Required fields.');
		}
				
		$fields = array();
		
		
		$fields['Created'] = date('Y-m-d H:i:s');
		$fields['LastEdited'] = date('Y-m-d H:i:s');
		
		foreach ($data as $key =>  $val) {
			$fields[$key] = $Site->sanitize($val);
		}
		
		//check if email matches
		if($fields['esig1234'] != $fields['Email']) {
			return array('msg'=>'Emails did not match.');
		}
		
		//check if passwords reqs met
		if( strlen($fields['password1'])  < 8  ) {
			return array('msg'=>'Password must be at least 8 characters.');
		}			
		
		//check if passwords matches
		if($fields['password1'] != $fields['password2']) {
			return array('msg'=>'Passwords did not match.');
		}		
		
		//check if email exists
		$emailcheck = $this->getData("SiteUsers"," SiteEmail='".$fields['Email']."'");		
		if($emailcheck) {
			return array('msg'=>'Email already exists.');
		}
		
		//check if SiteUsername exists
		$emailcheck = $this->getData("SiteUsers"," SiteUsername='".$fields['SiteUsername']."'");		
		if($emailcheck) {
			return array('msg'=>'That username already exists or is reserved.');
		}
		
		//
		$fields['Type'] = 'Personal';
		
		$fields['SiteUsername'] = isset($fields['SiteUsername']) ? $fields['SiteUsername'] : $fields['Email'];
		$fields['SiteEmail'] = $fields['Email'];
		$fields['SitePassword'] = md5($fields['password1']);
		$fields['anAdmin'] = 0;
		
		
		unset($fields['esig1234']);
		unset($fields['Email']);		
		unset($fields['password1']);
		unset($fields['password2']);
		
		$new_user = $this->insertData('SiteUsers',$fields);
			
		return $new_user;
	}
	
	
	

}
