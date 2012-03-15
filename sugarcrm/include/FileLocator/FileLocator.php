<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2011 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/

require_once 'include/FileLocator/FileLocatorInterface.php';

/**
 * A simple File Locator class. When created you pass in an array of paths for it to search.
 *
 */
class FileLocator implements FileLocatorInterface
{
    /**
     * Paths to check when we try and locate a file
     *
     * @var array
     */
    private $paths;

    /**
     * Constructor
     *
     * @param array $paths      Where we want to look for files at
     */
    public function __construct(array $paths)
    {
        $this->paths = (array)$paths;
    }


    /**
     * Try and find a file in the paths that were passed in when the object was created
     *
     * @param string $name          Name of the file we are looking for
     * @return bool|string          Returns the path of the file if one is found.  If not boolean FALSE is returned
     */
    public function locate($name)
    {
        if($this->isAbsolutePath($name) && file_exists($name))
        {
          return $name;
        }

        foreach($this->paths as $path)
        {
            $file = $path . DIRECTORY_SEPARATOR . $name;
            if(file_exists($file) && is_file($file))
            {
                return $file;
            }
        }
        return false;
    }

    /**
     * Set new Paths to check
     *
     * @param array $paths
     */
    public function setPaths($paths)
    {
        $this->paths = (array)$paths;
    }

    /**
     * Return the current set paths
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Check to see if the file contains an absolute path to the file.
     *
     * @param string $file      The file to check if the path is an absolute path or not
     * @return bool             True if file is an Absolute Path; False if not.
     */
    private function isAbsolutePath($file)
    {
        if ($file[0] == '/' || $file[0] == '\\'
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] == ':'
                && ($file[2] == '\\' || $file[2] == '/')
            )
        ) {
            return true;
        }

        return false;
    }

}
