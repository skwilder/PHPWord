<?php

class Default_ErrorController extends Zend_Controller_Action
{

    public function init ()
    {
    }

    public function errorAction ()
    {
        $errors = $this->_getParam('error_handler');
        
        $forwardToAction = 'page-not-found';
        switch ($errors->type)
        {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE :
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER :
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION :
                // 404 error -- controller or action not found
                $this->logAndPrepareExceptions($errors, Zend_Log::NOTICE);
                break;
            default :
                switch ($errors->exception->getCode())
                {
                    case 403 :
                        // Access Denied!
                        $forwardToAction = 'not-authorized';
                        break;
                    default :
                        // Application Error
                        $forwardToAction = 'application-error';
                        $this->logAndPrepareExceptions($errors, Zend_Log::CRIT);
                        break;
                }
                break;
        }
        
        $this->_forward($forwardToAction);
    }

    /**
     * Gets the appropriate Zend_Log facility, or false if none are registered.
     *
     * @return boolean Zend_Log
     */
    public function getLog ()
    {
        if (! Zend_Registry::isRegistered("Zend_Log"))
        {
            return false;
        }
        return Zend_Registry::get("Zend_Log");
    }

    /**
     * Logs the error and prepares the view with the appropriate information
     *
     * @param unknown_type $errors            
     * @param unknown_type $priority            
     */
    public function logAndPrepareExceptions ($errors, $priority)
    {
        /*
         * Generate a uid just in case two exceptions happen at the exact same time on different threads and we end up
         * getting mixed lines of a different exception
         */
        $uid = uniqid();
        $this->view->uid = $uid;
        $exceptions = array ();
        
        $ex = $errors->exception;
        
        // Declare the start of the trace
        
        // Loop through all the exceptions, and log them
        do
        {
            $exceptions [] = $ex;
        }
        while ( ! is_null($ex = $ex->getPrevious()) );
        
        // Conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true)
        {
            $this->view->exceptions = $exceptions;
            //$this->_helper->viewRenderer('error');
        }
        
        $this->view->request = $errors->request;
    }

    public function applicationErrorAction ()
    {
        $this->getResponse()->setHttpResponseCode(500);
    }

    public function pageNotFoundAction ()
    {
        $this->getResponse()->setHttpResponseCode(404);
    }

    public function notAuthorizedAction ()
    {
        $this->getResponse()->setHttpResponseCode(403);
    }
}

