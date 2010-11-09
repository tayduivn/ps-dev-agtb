<?php
// FILE SUGARCRM flav=pro ONLY 

class Bug40574Test2 extends Sugar_PHPUnit_Framework_TestCase
{
    private $pdfCacheDir;
    private $report_id;
    
    private function recursive_rmdir($dir)
    {
        if (is_dir($dir))
        { 
            $objects = scandir($dir); 
            foreach ($objects as $object)
            { 
                if ($object != "." && $object != "..")
                { 
                    if (filetype($dir."/".$object) == "dir")
                    {
                        $this->recursive_rmdir($dir."/".$object);
                    }
                    else
                    {
                        unlink($dir."/".$object);
                    } 
                } 
            } 
            reset($objects); 
            rmdir($dir); 
        } 
    }
    
    private function removePdfCacheDir()
    {
        $this->recursive_rmdir($this->pdfCacheDir);
    }
    
    private function restorePdfCacheDir()
    {
        $this->recursive_rmdir($this->pdfCacheDir);
        mkdir($this->pdfCacheDir);
    }
    
	public function setUp() 
    {
        $this->pdfCacheDir = $GLOBALS['sugar_config']['cache_dir'].'pdf/';
        
        $report = new SavedReport();
        $report->name = 'SDizzle';
        $report->save(false);
        $this->report_id = $report->id;
    }
	
	public function tearDown()
	{
	    $GLOBALS['db']->query("DELETE FROM saved_reports WHERE id = '{$this->report_id}'");
	}

	function testTCPDFCacheDirCreation()
	{
        $this->removePdfCacheDir();
        
        $GLOBALS['module'] = 'Contacts';
        $report = new SavedReport();
        $report->retrieve($this->report_id);
        
	    require_once('modules/Reports/templates/templates_tcpdf.php');
	    $pdf = preprocess('default', $report);
	    $filename = process($pdf, 'SDizzle', false);
	    $this->assertFileExists($this->pdfCacheDir, "TCPDF: PDF Cache directory did not exist when it should have");
	    
        $this->removePdfCacheDir();
	    $this->restorePdfCacheDir();
	}

}

