<?php
/**
 * Created by JetBrains PhpStorm.
 * User: admin
 * Date: 7/21/11
 * Time: 11:58 AM
 * To change this template use File | Settings | File Templates.
 */
require_once('service/core/REST/SugarRest.php');

class SugarRestDecorator extends SugarRest{
    protected $decoratedClass;

    public function __construct($decoratedClass){
        $this->decoratedClass = $decoratedClass;
	}

    public function serve(){
        return $this->decoratedClass->serve();
    }
}
 
