<?php
// FILE SUGARCRM flav=pro ONLY 

class Bug40574Test1 extends Sugar_PHPUnit_Framework_TestCase
{
    private $pdfCacheDir;
    
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
	}
	
	public function tearDown()
	{
	}

	function testEZPDFCacheDirCreation()
	{
        $this->removePdfCacheDir();
        
	    require_once('modules/Reports/templates/templates_ezpdf.php');
	    $pdf = preprocess_pdf();
	    $filename = postprocess_pdf($pdf, 'SDizzle', false);
	    $this->assertFileExists($this->pdfCacheDir, "EZPDF: PDF Cache directory did not exist when it should have");
	    
        $this->removePdfCacheDir();
	    $this->restorePdfCacheDir();
	}
	
}

