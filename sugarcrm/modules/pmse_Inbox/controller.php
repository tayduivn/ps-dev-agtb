<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'engine/PMSE.php';
//require_once 'engine/PMSEEngine.php';
require_once('modules/pmse_Inbox/engine/PMSEImageGenerator.php');
require_once('modules/pmse_Inbox/clients/base/api/PMSEEngineApi.php');

class pmse_InboxController extends SugarController
{

    /**
     *
     * @var type
     */
    protected $ajaxApi;
    /**
     *
     * @var type
     * @deprecated since version pmse2
     */
    protected $accessManager;
    /**
     *
     * @var type
     * @deprecated since version pmse2
     */
    protected $licenseManager;

    /**
     *
     * @var type
     */
    protected $beanFactory;
//    protected $engine;

    /**
     *
     * @var type
     */
    protected $moduleName;

    /**
     *
     * @var type
     */
    protected $modulePath;

    public function __construct()
    {
        $pmse = PMSE::getInstance();
        $this->moduleName = $pmse->getModuleName();
        $this->modulePath = $pmse->getModulePath();

//        $this->engine = new PMSEEngine();
//        $this->accessManager = new PMSEAccessManager();
//        $this->licenseManager = new PMSELicenseManager();
        //$this->beanFactory = new BeanFactory();//ADAMBeanFactory();
        parent::SugarController();
    }

//    public function action_importProject()
//    {
//        if ($this->accessManager->casesCanBeExecuted() && $this->accessManager->canAssignAdminDashlets()) {
//            $this->view = 'importproject';
//        } else {
//            echo translate('LBL_PMSE_MESSAGE_LICENSEEXPIRED', $this->moduleName);
//        }
//    }

//    public function action_importBusinessRule()
//    {
//        if ($this->accessManager->casesCanBeExecuted() && $this->accessManager->canAssignAdminDashlets()) {
//            $this->view = 'importbusinessrule';
//        } else {
//            echo translate('LBL_PMSE_MESSAGE_LICENSEEXPIRED', $this->moduleName);
//        }
//    }

//    public function action_importEmailTemplate()
//    {
//        if ($this->accessManager->casesCanBeExecuted() && $this->accessManager->canAssignAdminDashlets()) {
//            $this->view = 'importemailtemplate';
//        } else {
//            echo translate('LBL_PMSE_MESSAGE_LICENSEEXPIRED', $this->moduleName);
//        }
//    }

//    public function action_exportBusinessRuleFile()
//    {
//        global $current_user;
//        $exporterObject = new ADAMBusinessRuleExporter();
//        $exporterObject->exportProject();
//        header('Location: ./index.php');
//    }

//    public function action_exportEmailTemplateFile()
//    {
//        global $current_user;
//        $exporterObject = new ADAMEmailTemplateExporter();
//        $exporterObject->exportProject();
//        header('Location: ./index.php');
//    }

    public function action_studio_configuration()
    {
        $this->view = 'studio_configuration';
    }

//    public function action_export_project_file()
//    {
//        global $current_user;
//        $exporterObject = new ADAMProjectExporter();
//        $exporterObject->exportProject();
//        header('Location: ./index.php');
//    }

    public function action_showCase()
    {
        //if ($this->accessManager->casesCanBeExecuted()) {
        $this->view = 'showCase';
        //} else {
        //    $this->view = 'limitReached';
        //}
    }

    public function action_noShowCase()
    {
//        if ($this->accessManager->casesCanBeExecuted()) {
        $this->view = 'noShowCase';
//        } else {
//            $this->view = 'limitReached';
//        }
    }

//    public function action_caseslist()
//    {
//        $this->view = 'caseslist';
//    }

//    public function action_about()
//    {
//        $this->view = 'about';
//    }

    /**
     *
     * @global type $beanList
     * @global type $current_user
     * @global type $beanFiles
     * @deprecated since version pmse2
     * @codeCoverageIgnore
     */
    public function action_routeCase()
    {
        $data = $_REQUEST;
        $data['frm_action'] = $data['Type'];
        $data['taskName'] = '';
        $engineApi = new PMSEEngineApi();
        $result = $engineApi->engineRoute(array(), $data);
        header('Location: index.php');
    }
    
    public function action_showPNG()
    {
//        $case = new PMSEImageGenerator();
//        $img = $case->get_image($_REQUEST['case']);
//        header('Content-Type: image/png');
//        imagepng($img);
//        imagedestroy($img);
        require_once('modules/pmse_Inbox/clients/base/api/PMSEImageGeneratorApi.php');
        header('Content-Type: image/png');
        $img = new PMSEImageGeneratorApi();
        $img->getProcessImage(null, array('record' => $_REQUEST['case']));
    }

    public function action_showHistoryEntries()
    {
        $this->view = 'showHistoryEntries';
    }

    public function action_showNotes()
    {
        $this->view = 'showNotes';
    }


//    public function action_licenseManager()
//    {
//        $this->view = 'licensemanager';
//    }
//
//    public function action_getLicenseData()
//    {
//        $result = new stdClass();
//
//        if (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
//            $result = $this->licenseManager->getLicenseData($_REQUEST['id']);
//            echo json_encode($result);
//        } else {
//            $result->success = false;
//            $result->message = 'Id field is empty.';
//            echo json_encode($result);
//        }
//    }
//
//    public function action_processLicense()
//    {
//        $result = new stdClass();
//        if (isset($_REQUEST['license']) && $_REQUEST['license'] != '') {
//            $result = $this->licenseManager->processLicense($_REQUEST['license']);
//            echo json_encode($result);
//        } else {
//            $result->success = false;
//            $result->message = 'License field is empty.';
//            echo json_encode($result);
//        }
//    }
//
//    public function action_processActivationCode()
//    {
//        $result = new stdClass();
//        if (isset($_REQUEST['activationCode']) && $_REQUEST['activationCode'] != '') {
//            $result = $this->licenseManager->processActivationCode(trim($_REQUEST['activationCode']));
//            echo json_encode($result);
//        } else {
//            $result->success = false;
//            $result->message = 'Activation Code field is empty.';
//            echo json_encode($result);
//        }
//    }

    public function action_testLogger()
    {
        require_once 'modules/pmse_Inbox/engine/PMSELogger.php';
        $log = PMSELogger::getInstance();
        $log->emergency('This is a LogLevel::EMERGENCY');
        $log->alert('This is a LogLevel::ALERT');
        $log->critical('This is a LogLevel::CRITICAL');
        $log->error('This is a LogLevel::ERROR');
        $log->warning('This is a LogLevel::WARNING');
        $log->notice('This is a LogLevel::NOTICE');
        $log->info('This is a LogLevel::INFO');
        $log->debug('This is a LogLevel::DEBUG');

        $params['tags'] = array(
            array(
                "id" => "seed_sally_id",
                "name" => "Sally Bronsen",
                "module" => "Users"
            ),
            array(
                "id" => "seed_sarah_id",
                "name" => "Sarah Smith",
                "module" => "Users"
            ),
            array(
                "id" => "52f46f19-7a10-4dd5-28ed-53b4671f964d",
                "name" => "Stephanie Plunk",
                "module" => "Leads"
            )
        );

        $params['module_name'] = 'pmse_Inbox';

        $log->debug('This is array', $params);

        $log->activity('This a message user: %0  whit %1 for the record: %2 end of the message.', $params);
    }

    public function action_testImage()
    {
        require_once 'modules/pmse_Inbox/engine/PMSEImageGenerator.php';
        $image = new PMSEImageGenerator();

        $img = $image->get_image(8);
        header('Content-Type: image/png');
        imagepng($img, 'D:\Projects\TEST.png');
        imagedestroy($img);
        //echo '<img src="D:\Projects\TEST.png">';
    }
}