<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 ********************************************************************************/
/**
 * This defines a bunch of core classes and where they can be loaded from
 * Only non PSR-0 classes need to be named here, other classes will be found automatically
 */
$class_map = array(
    'XTemplate'=>'vendor/XTemplate/xtpl.php',
    'Javascript'=>'include/javascript/javascript.php',
    'ListView'=>'include/ListView/ListView.php',
    'Sugar_Smarty'=>'include/Sugar_Smarty.php',
    'CustomSugarView' => 'custom/include/MVC/View/SugarView.php',
    'Sugar_Smarty' => 'include/Sugar_Smarty.php',
    'HTMLPurifier_Bootstrap' => 'vendor/HTMLPurifier/HTMLPurifier.standalone.php',
    'SugarSearchEngineFullIndexer'=>'include/SugarSearchEngine/SugarSearchEngineFullIndexer.php',
    'SugarSearchEngineSyncIndexer'=>'include/SugarSearchEngine/SugarSearchEngineSyncIndexer.php',
    'SugarCurrency'=>'include/SugarCurrency/SugarCurrency.php',
    'SugarRelationshipFactory' => 'data/Relationships/RelationshipFactory.php',
    'DBManagerFactory' => 'include/database/DBManagerFactory.php',
    'Localization' => 'include/Localization/Localization.php',
    'JsAlerts' => 'include/javascript/jsAlerts.php',
    'TimeDate' => 'include/TimeDate.php',
    'SugarDateTime' => 'include/SugarDateTime.php',
    'SugarBean' => 'data/SugarBean.php',
    'LanguageManager' => 'include/SugarObjects/LanguageManager.php',
    'VardefManager' => 'include/SugarObjects/VardefManager.php',
    'MetaDataManager' => 'include/MetaDataManager/MetaDataManager.php',
    'TemplateText' => 'modules/DynamicFields/templates/Fields/TemplateText.php',
    'TemplateField' => 'modules/DynamicFields/templates/Fields/TemplateField.php',
    'SugarEmailAddress' => 'include/SugarEmailAddress/SugarEmailAddress.php',
    'JSON' => 'include/JSON.php',
    'LoggerManager' => 'include/SugarLogger/LoggerManager.php',
    'ACLController' => 'modules/ACL/ACLController.php',
    'ACLJSController' => 'modules/ACL/ACLJSController.php',
    'Administration' => 'modules/Administration/Administration.php',
    'OutboundEmail' => 'include/OutboundEmail/OutboundEmail.php',
    'MailerFactory' => 'modules/Mailer/MailerFactory.php',
    'LogicHook' => 'include/utils/LogicHook.php',
    'SugarTheme' => 'include/SugarTheme/SugarTheme.php',
    'SugarThemeRegistry' => 'include/SugarTheme/SugarTheme.php',
    'SugarModule' => 'include/MVC/SugarModule.php',
    'SugarApplication' => 'include/MVC/SugarApplication.php',
    'ControllerFactory' => 'include/MVC/Controller/ControllerFactory.php',
    'ViewFactory' => 'include/MVC/View/ViewFactory.php',
    'BeanFactory' => 'data/BeanFactory.php',
    'Audit' => 'modules/Audit/Audit.php',
    'Link2' => 'data/Link2.php',
);

