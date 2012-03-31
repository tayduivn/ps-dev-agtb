<?php
//FILE SUGARCRM flav=ent ONLY
if (! defined ( 'sugarEntry' ) || ! sugarEntry)
    die ( 'Not A Valid Entry Point' ) ;
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
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 * $Id: additionalDetails.php 13782 2006-06-06 17:58:55Z majed $
 *********************************************************************************/

require_once 'modules/ModuleBuilder/parsers/views/GridLayoutMetaDataParser.php' ;
require_once 'modules/ModuleBuilder/parsers/constants.php' ;

class  PortalGridLayoutMetaDataParser extends GridLayoutMetaDataParser
{

    static $variableMap = array (
    	MB_PORTALEDITVIEW => 'EditView' ,
    	MB_PORTALDETAILVIEW => 'DetailView' ,
    	) ;

    /**
     * here we convert from internal metadata format to file (canonical) metadata
     * @param $panels
     * @param $fielddefs
     */
//    protected function _convertToCanonicalForm($panels , $fielddefs)
//    {
//
//    }

    /**
     * here we convert from file (canonical) metadata => internal metadata format
     * @param $panels
     * @param $fielddefs
     * @return array $internalPanels
     */
    protected function _convertFromCanonicalForm($panels , $fielddefs)
    {
        // canonical form has format:
        // $panels[n]['label'] = label for panel n
        //           ['fields'] = array of fields


        // internally we want:
        // $panels[label for panel] = fields of panel in rows,cols format

        $internalPanels = array();
        foreach ($panels as $n => $panel) {
            $pLabel = !empty($panel['label']) ? $panel['label'] : $n;

            // going from a list of fields to putting them in rows,cols format.
            $internalFieldRows = array();
            $row = array();
            foreach ($panel['fields'] as $field) {
                // try to find the column span of the field. It can range from 1 to max columns of the panel.
                $colspan = isset($field['displayParams']['colspan']) ? $field['displayParams']['colspan'] : 1;
                $colspan = min($colspan, $this->getMaxColumns()); // we can't put in a field wider than the panel.
                $cols_left = $this->getMaxColumns() - count($row);

                if ($cols_left < $colspan) {
                    // add $cols_left of (empty) to $row and put it in
                   for($i=0; $i < $cols_left; $i++) {
                       $row[] = MBConstants::$EMPTY;
                   }
                   $internalFieldRows[] = $row;
                   $row = array();
                }

                // add field to row + enough (empty) to make it to colspan
                $row[] = empty($field) ? $this->FILLER : $field;
                for($i=0; $i < $colspan-1; $i++){
                    $row[] = MBConstants::$EMPTY;
                }
            }

            // add the last incomplete row if necessary
            if (!empty($row)) {
                $cols_left = $this->getMaxColumns() - count($row);
                // add $cols_left of (empty) to $row and put it in
                for($i=0; $i < $cols_left; $i++) {
                    $row[] = MBConstants::$EMPTY;
                }
                $internalFieldRows[] = $row;
            }
            $internalPanels[$pLabel] = $internalFieldRows;
        }




        return $internalPanels;
    }

}

?>