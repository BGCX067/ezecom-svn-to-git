<?php

class ErrorController extends Zend_Controller_Action
{

    public function init(){
         $this->_helper->layout()->setLayout('error'); // sets a different layout     
    }
	
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        switch ($errors->type) { 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                
                if(getenv('APPLICATION_ENV')=="development"):
					 $this->view->message = 'Page not found';                
                elseif(getenv('APPLICATION_ENV')=="production"):
                	//$this->_redirect('/index/');
                endif;	
               	break;
            default:
                // application error 
                $this->getResponse()->setHttpResponseCode(500);
                if(getenv('APPLICATION_ENV')=="development"):
					 $this->view->message = 'Application Error';                
                elseif(getenv('APPLICATION_ENV')=="production"):
					$this->view->message = 'Application Error';      
                	//$this->_redirect('/index/');
                endif;	
               	break;
        }
        
        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
    }


}

