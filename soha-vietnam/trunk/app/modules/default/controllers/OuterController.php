<?php

/**
 * application outer controller
 *
 * @copyright  Copyright (c) 2009 Community Factory Inc. (http://communityfactory.com)
 * @create    2009/08/07    HLJ
 */
class OuterController extends Zend_Controller_Action
{

    /**
     * index Action
     *
     */
    public function indexAction()
    {
    	echo 'outer pages';
    	exit;
    }

	/**
     * calc Action
     *
     */
    public function calcAction()
    {
    	echo 'calc pages';
    	exit;
    }

    /**
     * magic function
     *   if call the function is undefined,then echo undefined
     *
     * @param string $methodName
     * @param array $args
     * @return void
     */
    function __call($methodName, $args)
    {
        echo 'undefined method name: ' . $methodName;
        exit;
    }

}