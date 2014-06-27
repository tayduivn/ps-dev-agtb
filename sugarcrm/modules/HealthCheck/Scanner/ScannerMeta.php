<?php

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

/**
 *
 * HealthCheck Scanner Metadata
 *
 */
class ScannerMeta
{
    const FLAG_GREEN = 1;
    const FLAG_YELLOW = 2;
    const FLAG_RED = 3;

    // plain vanilla sugar
    const VANILLA = 'A';
    // studio mods
    const STUDIO = 'B';
    // studio and MB mods
    const STUDIO_MB = 'C';
    // studio and MB mods that need to BWC some modules
    const STUDIO_MB_BWC = 'D';
    // heavy customization, needs fixes
    const CUSTOM = 'E';
    // manual customization required
    const MANUAL = 'F';
    // already on 7
    const UPGRADED = 'G';

    /**
     *
     * Scan Meta Data
     * @var array
     */
    protected $meta = array(

        // skeleton
        // '100' => array(
        //    'report' => '', // report id
        //    'bucket' => self::STUDIO_MB,
        //    'flag' => self::FLAG_YELLOW, // optional, default will be added
        //    'kb' => false, // optional, default will be added
        //    'tickets' => array(), // optional, default will be added
        //    'scripts' => array(), // optional, default will be added
        //),

        // BUCKET B
        101 => array(
            'report' => 'hasStudioHistory',
            'bucket' => self::STUDIO,
        ),
        102 => array(
            'report' => 'hasExtensions',
            'bucket' => self::STUDIO,
        ),
        103 => array(
            'report' => 'hasCustomVardefs',
            'bucket' => self::STUDIO,
        ),
        104 => array(
            'report' => 'hasCustomLayoutdefs',
            'bucket' => self::STUDIO,
        ),
        105 => array(
            'report' => 'hasCustomViewdefs',
            'bucket' => self::STUDIO,
        ),

        // BUCKET C
        201 => array(
            'report' => 'notStockModule',
            'bucket' => self::STUDIO_MB,
        ),

        // BUCKET D
        301 => array(
            'report' => 'toBeRunAsBWC',
            'bucket' => self::STUDIO_MB_BWC,
        ),
        302 => array(
            'report' => 'unknownFileViews',
            'bucket' => self::STUDIO_MB_BWC,
        ),
        303 => array(
            'report' => 'nonEmptyFormFile',
            'bucket' => self::STUDIO_MB_BWC,
        ),
        304 => array(
            'report' => 'isNotMBModule',
            'bucket' => self::STUDIO_MB_BWC,
        ),
        305 => array(
            'report' => 'badVardefsKey',
            'bucket' => self::STUDIO_MB_BWC,
        ),
        306 => array(
            'report' => 'badVardefsRelate',
            'bucket' => self::STUDIO_MB_BWC,
        ),
        307 => array(
            'report' => 'badVardefsLink',
            'bucket' => self::STUDIO_MB_BWC,
        ),
        308 => array(
            'report' => 'vardefHtmlFunction',
            'bucket' => self::STUDIO_MB_BWC,
        ),
        309 => array(
            'report' => 'badMd5',
            'bucket' => self::STUDIO_MB_BWC,
        ),
        310 => array(
            'report' => 'unknownFile',
            'bucket' => self::STUDIO_MB_BWC,
        ),

        // BUCKET E
        401 => array(
            'report' => 'vendorFilesInclusion',
            'bucket' => self::CUSTOM,
        ),
        402 => array(
            'report' => 'badModule',
            'bucket' => self::CUSTOM,
        ),
        403 => array(
            'report' => 'logicHookAfterUIFrame',
            'bucket' => self::CUSTOM,
        ),
        404 => array(
            'report' => 'logicHookAfterUIFooter',
            'bucket' => self::CUSTOM,
        ),
        405 => array(
            'report' => 'incompatIntegration',
            'bucket' => self::CUSTOM,
        ),
        406 => array(
            'report' => 'hasCustomViews',
            'bucket' => self::CUSTOM,
        ),
        407 => array(
            'report' => 'hasCustomViewsModDir',
            'bucket' => self::CUSTOM,
        ),
        408 => array(
            'report' => 'extensionDir',
            'bucket' => self::CUSTOM,
        ),
        409 => array(
            'report' => 'foundCustomCode',
            'bucket' => self::CUSTOM,
        ),
        410 => array(
           'report' => 'maxFieldsView',
            'bucket' => self::CUSTOM,
        ),
        411 => array(
            'report' => 'subPanelWithFunction',
            'bucket' => self::CUSTOM,
        ),
        412 => array(
            'report' => 'badSubpanelLink',
            'bucket' => self::CUSTOM,
        ),
        413 => array(
            'report' => 'unknownWidgetClass',
            'bucket' => self::CUSTOM,
        ),
        414 => array(
            'report' => 'unknownField',
            'bucket' => self::CUSTOM,
        ),
        415 => array(
            'report' => 'badHookFile',
            'bucket' => self::CUSTOM,
        ),
        416 => array(
            'report' => 'byRefInHookFile',
            'bucket' => self::CUSTOM,
        ),
        417 => array(
            'report' => 'incompatModule',
            'bucket' => self::CUSTOM,
        ),
        418 => array(
            'report' => 'subpanelLinkNonExistModule',
            'bucket' => self::CUSTOM,
        ),
        419 => array(
            'report' => 'badVardefsKeyCustom',
            'bucket' => self::CUSTOM,
        ),
        420 => array(
            'report' => 'badVardefsRelateCustom',
            'bucket' => self::CUSTOM,
        ),
        421 => array(
            'report' => 'badVardefsLinkCustom',
            'bucket' => self::CUSTOM,
        ),
        422 => array(
            'report' => 'vardefHtmlFunctionCustom',
            'bucket' => self::CUSTOM,
        ),
        423 => array(
            'report' => 'badVardefsCustom',
            'bucket' => self::CUSTOM,
        ),
        424 => array(
            'report' => 'inlineHtmlCustom',
            'bucket' => self::CUSTOM,
        ),
        425 => array(
            'report' => 'foundEchoCustom',
            'bucket' => self::CUSTOM,
        ),
        426 => array(
            'report' => 'foundPrintCustom',
            'bucket' => self::CUSTOM,
        ),
        427 => array(
            'report' => 'foundDieExitCustom',
            'bucket' => self::CUSTOM,
        ),
        428 => array(
            'report' => 'foundPrintRCustom',
            'bucket' => self::CUSTOM,
        ),
        429 => array(
            'report' => 'foundVarDumpCustom',
            'bucket' => self::CUSTOM,
        ),
        430 => array(
            'report' => 'foundOutputBufferingCustom',
            'bucket' => self::CUSTOM,
        ),

        // BUCKET F
        501 => array(
            'report' => 'missingFile',
            'bucket' => self::MANUAL,
        ),
        502 => array(
            'report' => 'md5Mismatch',
            'bucket' => self::MANUAL,
        ),
        503 => array(
            'report' => 'sameModuleName',
            'bucket' => self::MANUAL,
        ),
        504 => array(
            'report' => 'fieldTypeMissing',
            'bucket' => self::MANUAL,
        ),
        505 => array(
            'report' => 'typeChange',
            'bucket' => self::MANUAL,
        ),
        506 => array(
            'report' => 'thisUsage',
            'bucket' => self::MANUAL,
        ),
        507 => array(
            'report' => 'badVardefs',
            'bucket' => self::MANUAL,
        ),
        508 => array(
            'report' => 'inlineHtml',
            'bucket' => self::MANUAL,
        ),
        509 => array(
            'report' => 'foundEcho',
            'bucket' => self::MANUAL,
        ),
        510 => array(
            'report' => 'foundPrint',
            'bucket' => self::MANUAL,
        ),
        511 => array(
            'report' => 'foundDieExit',
            'bucket' => self::MANUAL,
        ),
        512 => array(
            'report' => 'foundPrintR',
            'bucket' => self::MANUAL,
        ),
        513 => array(
            'report' => 'foundVarDump',
            'bucket' => self::MANUAL,
        ),
        514 => array(
            'report' => 'foundOutputBuffering',
            'bucket' => self::MANUAL,
        ),
        515 => array(
            'report' => 'scriptFailure',
            'bucket' => self::MANUAL,
        ),

        // Bucket G
        901 => array(
            'report' => 'alreadyUpgraded',
            'bucket' => self::UPGRADED,
        ),

        // Catch all meta
        999 => array(
            'report' => 'unknownFailure',
            'bucket' => self::MANUAL,
        ),
    );

    protected $metaByReportId = array();

    /**
     *
     * Default flag --> bucket mapping
     * @var array
     */
    protected $defaultFlagMap = array(
        self::VANILLA => self::FLAG_GREEN,
        self::STUDIO => self::FLAG_YELLOW,
        self::STUDIO_MB => self::FLAG_YELLOW,
        self::STUDIO_MB_BWC => self::FLAG_YELLOW,
        self::CUSTOM => self::FLAG_YELLOW,
        self::MANUAL => self::FLAG_RED,
        self::UPGRADED => self::FLAG_GREEN,
    );

    /**
     *
     * @var array $mod_strings
     */
    protected $modStrings;

    /**
     *
     * @var ScannerMeta
     */
    protected static $instance;

    /**
     *
     */
    public function __construct()
    {
        $this->loadModStrings();
        $this->createMetaByReportId();
    }

    /**
     *
     * @return ScannerMeta
     */
    public static function get()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     *
     */
    protected function createMetaByReportId()
    {
        foreach ($this->meta as $id => $meta) {
            if (isset($meta['report'])) {
                $reportId = $meta['report'];
                if (isset($this->metaByReportId[$reportId])) {
                    throw new RuntimeException("Non-unique report id {$reportId}");
                }
                $this->metaByReportId[$reportId] = $id;
            }
        }
    }

    /**
     *
     * @param string $id Scan id
     * @return array|boolean
     */
    public function getMeta($id, $params = array())
    {
        if (!isset($this->meta[$id])) {
            return false;
        }

        $meta = $this->meta[$id];

        // add scan id
        $meta['id'] = $id;

        // add labels from modStrings
        $meta['log'] = $this->getModString("LBL_SCAN_{$id}_LOG", $params);
        $meta['title'] = $this->getModString("LBL_SCAN_{$id}_TITLE", $params);
        $meta['descr'] = $this->getModString("LBL_SCAN_{$id}_DESCR", $params);

        // set defaults
        if (!isset($meta['flag'])) {
            $meta['flag'] = $this->getDefaultFlag($meta['bucket']);
        }
        if (!isset($meta['kb'])) {
            $meta['kb'] = false;
        }
        if (!isset($meta['tickets'])) {
            $meta['tickets'] = array();
        }
        if (!isset($meta['scripts'])) {
            $meta['scripts'] = array();
        }

        return $meta;
    }

    public function getDefaultFlag($bucket)
    {
        return $this->defaultFlagMap[$bucket];
    }

    /**
     *
     * @param unknown $reportId
     * @return array|boolean
     */
    public function getMetaFromReportId($reportId, $params = array())
    {
        if (isset($this->metaByReportId[$reportId])) {
            return $this->getMeta($this->metaByReportId[$reportId], $params);
        }
        return false;
    }

    /**
     *
     * @param string $label
     * @return string
     */
    protected function getModString($label, $params = array())
    {
        if (!empty($this->modStrings[$label])) {
            $label = $this->modStrings[$label];
        }
        return vsprintf($label, $params);
    }

    /**
     *
     */
    protected function loadModStrings()
    {
        if (is_callable('return_module_language') && isset($GLOBALS['current_language'])) {
            $this->modStrings = return_module_language($GLOBALS['current_language'], 'HealthCheck');
        } else {
            include __DIR__ . '/../language/en_us.lang.php';
            $this->modStrings = $mod_strings;
        }
    }
}
