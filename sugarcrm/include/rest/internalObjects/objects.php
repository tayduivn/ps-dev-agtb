<?php

include_once("include/rest/RestObjectInterface.php");
include_once("RestError.php");
include_once("RestUtils.php");
include_once("RestObject.php");
include_once("include/MetaDataManager/MetaDataManager.php");
include_once("service/core/SoapHelperWebService.php");

class Objects extends RestObject implements IRestObject {

    public $helper = null;

    function __construct() {
        parent::__contrust();
    }

    public function execute() {

    }

}