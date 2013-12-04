<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Before 7.1.5, SidecarThemes were defined in a variables.less file.
 * By 7.1.5, SidecarThemes are defined in a variables.php file
 */
class SugarUpgradeConvertPortalTheme extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_CUSTOM;
    public $version = "7.1.5";

    public function run()
    {
        foreach(glob('custom/themes/clients/*/*/variables.less') as $customTheme) {
            $path = pathinfo($customTheme, PATHINFO_DIRNAME);
            $variables = $this->parseFile($path . '/variables.less');

            // Convert to new defs
            $lessdefs = array(
                'colors' => $variables['hex']
            );

            // Write new defs
            $write = "<?php\n" .
                '// created: ' . date('Y-m-d H:i:s') . "\n" .
                '$lessdefs = ' .
                var_export_helper($lessdefs) . ';';
            sugar_file_put_contents($path . '/variables.php', $write);

            // Delete old defs
            $this->fileToDelete($path . '/variables.less');
        }
    }
    
    /**
     * Does a preg_match_all on a less file to retrieve a type of less variables
     *
     * @param string $pattern Pattern to search
     * @param string $input Input string
     *
     * @return array Variables found
     */
    private function parseLessVars($pattern, $input)
    {
        $output = array();
        preg_match_all($pattern, $input, $match, PREG_PATTERN_ORDER);
        foreach ($match[1] as $key => $lessVar) {
            $output[$lessVar] = $match[3][$key];
        }
        return $output;
    }

    /**
     * Parse a less file to retrieve all types of less variables
     * - 'mixins' defs         @varName:      mixinName;
     * - 'hex' colors          @varName:      #aaaaaa;
     * - 'rgba' colors         @varName:      rgba(0,0,0,0);
     * - 'rel' related colors  @varName:      @relatedVar;
     * - 'bg'  backgrounds     @varNamePath:  "./path/to/img.jpg";
     *
     * @param string $file The file to parse
     *
     * @return array Variables found by type
     */
    private function parseFile($file)
    {
        $contents = file_get_contents($file);
        $output = array();
        $output['mixins'] = $this->parseLessVars("/@([^:|@]+):(\s+)([^\#|@|\(|\"]*?);/", $contents);
        $output['hex'] = $this->parseLessVars("/@([^:|@]+):(\s+)(\#.*?);/", $contents);
        $output['rgba'] = $this->parseLessVars("/@([^:|@]+):(\s+)(rgba\(.*?\));/", $contents);
        $output['rel'] = $this->parseLessVars("/@([^:|@]+):(\s+)(@.*?);/", $contents);
        $output['bg'] = $this->parseLessVars("/@([^:|@]+Path):(\s+)\"(.*?)\";/", $contents);
        return $output;
    }
}
