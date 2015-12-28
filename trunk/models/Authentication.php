<?php
class Model_Authentication extends Zend_Controller_Action
{
    public function preDispatch()
    {
		$authsession = new Zend_Session_Namespace('authsession');
		if (!isset($authsession->logged_user)) {
			$this->_redirect('/authentication/login');
		} else {
			#check if user has access to this URL 
			$uriIsSet = array();
			foreach ($authsession->links as $link => $users_access) {
				if (!empty($link) && $link == substr($_SERVER['REQUEST_URI'], 0, strlen($link))) {
					$uriIsSet = array($link, $users_access);
					break;
				}
			}

			if ( !empty($uriIsSet) && !in_array($authsession->user_access, $uriIsSet[1]) ) {
				$this->_redirect($authsession->user_default_landing_page);
			}
		}
	}
}
