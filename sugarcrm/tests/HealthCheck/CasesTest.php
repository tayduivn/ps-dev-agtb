<?php

require_once 'HealthCheckScannerMeta.php';
require_once 'HealthCheckScanner.php';
require_once 'HealthCheck.php';

class HealthCheckCasesTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $currentDirectory = '';
    protected $currentPath = '';
    protected $cachedPath = '';

    /** @var HealthCheckScannerCasesTestWrapper */
    protected $scanner = null;
    /** @var HealthCheckCasesTestWrapper */
    protected $healthCheck = null;

    public function setUp()
    {
        parent::setUp();
        $this->currentDirectory = getcwd();
        $this->currentPath = ini_get('include_path');
        do {
            $this->cachedPath = sugar_cached(md5(microtime(true)));
        } while (is_dir($this->cachedPath));
        sugar_mkdir($this->cachedPath);
    }

    public function tearDown()
    {
        chdir($this->currentDirectory);
        $this->currentDirectory = '';

        rmdir_recursive($this->cachedPath);
        $this->cachedPath = '';

        ini_set('include_path', $this->currentPath);
        $this->currentPath = '';

        if ($this->scanner instanceof HealthCheckScannerCasesTestWrapper) {
            $this->scanner->tearDown();
        }

        parent::tearDown();
    }

    /**
     * @dataProvider getCases
     */
    public function testCase($code, $case)
    {
        switch ($case) {
            case '402' :
            case '414' :
            case '416' :
            case '419' :
            case '420' :
            case '421' :
            case '422' :
            case '423' :
            case '424' :
            case '425' :
            case '426' :
            case '427' :
            case '428' :
            case '429' :
            case '430' :
            case '431' :
            case '432' :
                $this->markTestIncomplete('Noo');
                break;
        }

        if (!is_dir(__DIR__ . '/cases/' . $case)) {
            $this->fail('HealthCheck code ' . $code . ' case ' . $case . ' is not covered');
        }

        if ($case != '409') {
//            $this->markTestSkipped('Noo');
        }

        list($this->scanner, $this->healthCheck) = $this->getHealthCheckObjects($case);

        if (is_dir(__DIR__ . '/cases/' . $case . '/sugarcrm')) {
            copy_recursive(__DIR__ . '/cases/' . $case . '/sugarcrm', $this->cachedPath);
        }
        ini_set('include_path', realpath($this->cachedPath) . PATH_SEPARATOR . ini_get('include_path'));
        chdir($this->cachedPath);

        $this->healthCheck->run($this->scanner);

        $detectedStatuses = array();
        foreach ($this->scanner->getStatusLog() as $bucket) {
            foreach ($bucket as $log) {
                $detectedStatuses[] = $log['code'];
            }
        }
        $detectedStatuses = array_unique($detectedStatuses);
        $this->assertContains($code, $detectedStatuses, 'Requested status is not detected');
    }

    /**
     * @param $case
     * @return array
     */
    protected function getHealthCheckObjects($case)
    {
        $this->scanner = 'HealthCheckScannerCasesTestWrapper';
        if (is_file(__DIR__ . '/cases/' . $case . '/HealthCheckScanner.php')) {
            require_once __DIR__ . '/cases/' . $case . '/HealthCheckScanner.php';
            if (class_exists('S_' . $case . '_' . $this->scanner)) {
                $this->scanner = 'S_' . $case . '_' . $this->scanner;
            }
        }
        $this->scanner = new $this->scanner();

        $this->healthCheck = 'HealthCheckCasesTestWrapper';
        if (is_file(__DIR__ . '/cases/' . $case . '/HealthCheck.php')) {
            require_once __DIR__ . '/cases/' . $case . '/HealthCheck.php';
            if (class_exists('S_' . $case . '_' . $this->healthCheck)) {
                $this->healthCheck = 'S_' . $case . '_' . $this->healthCheck;
            }
        }
        $this->healthCheck = new $this->healthCheck();

        return array($this->scanner, $this->healthCheck);
    }

    static public function getCases()
    {
        $cases = array();
        foreach (HealthCheckScannerMetaCasesTestWrapper::getCodes() as $code) {
            $iterator = new DirectoryIterator(__DIR__ . '/cases');
            /** @var DirectoryIterator $pointer */
            $isUpdated = false;
            foreach ($iterator as $pointer) {
                if (!$pointer->isDir() || $pointer->isDot()) {
                    continue;
                }
                if (!preg_match('/^\d+(_[\w\d]+)?$/', $pointer->getFilename())) {
                    continue;
                }
                if ($pointer->getFilename() != $code && substr($pointer->getFilename(), 0, strlen($code) + 1) != $code . '_') {
                    continue;
                }
                $cases[] = array($code, $pointer->getFilename());
                $isUpdated = true;
            }
            if (!$isUpdated) {
                $cases[] = array($code, $code);
            }
        }

        return $cases;
    }
}
