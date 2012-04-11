<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
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

require_once 'modules/ModuleBuilder/parsers/views/WirelessGridLayoutMetaDataParser.php' ;
require_once 'modules/ModuleBuilder/parsers/constants.php' ;

class  PortalGridLayoutMetaDataParser extends WirelessGridLayoutMetaDataParser
{

    static $variableMap = array (
        //BEGIN SUGARCRM flav=ent ONLY
    	MB_PORTALEDITVIEW => 'EditView' ,
    	MB_PORTALDETAILVIEW => 'DetailView' ,
        //END SUGARCRM flav=ent ONLY
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
     */
//    protected function _convertFromCanonicalForm($panels , $fielddefs)
//    {
//
//    }

    /**
     * here we go from POST vars => internal metadata format
     * @param $fielddefs
     */
//    protected function _populateFromRequest(&$fielddefs)
//    {
//
//    }

    /**
     * Checks for the existence of the view variable for portal metadata
     *
     * @param array $viewdefs The viewdef array
     * @param string $view The view to check for
     * @return bool
     */
    public function hasViewVariable($viewdefs, $view) {
        $name = MetaDataFiles::getViewDefVar($view);
        $client = MetaDataFiles::getViewClient($view);
        return $name && $client && isset($viewdefs[$client]['views'][$name]);
    }

    /**
     * Gets the viewdefs for portal from the entire viewdef array
     *
     * @param array $viewdefs The full viewdef collection below $viewdefs[$module]
     * @param string $view The view to fetch the defs for
     * @return array
     */
    public function getDefsFromArray($viewdefs, $view) {
        return $this->hasViewVariable($viewdefs, $view) ? $viewdefs[MetaDataFiles::getViewClient($view)]['views'][MetaDataFiles::getViewDefVar($view)] : array();
    }

    /**
     * Gets panel defs from the viewdef array
     * @param array $viewdef The viewdef array
     * @return array
     */
    protected function getPanelsFromViewDef($viewdef) {
        $defs = $this->getDefsFromArray($viewdef, $this->_view);
        if (isset($defs['panels'])) {
    		return $defs['panels'];
    	}

        return array();
    }

    /*
     * Save a draft layout
     */
    function writeWorkingFile ()
    {
        $this->_populateFromRequest ( $this->_fielddefs ) ;
        $viewdefs = $this->_viewdefs ;

        $panels = each ( $this->_convertToCanonicalForm ( $this->_viewdefs [ 'panels' ] , $this->_fielddefs ) ) ;
        $viewdefs [ 'panels' ] = $panels [ 'value' ] ;
        $this->implementation->save ( array ( self::$variableMap [ $this->_view ] => $viewdefs ) ) ;
    }

    /*
     * Deploy the layout
     * @param boolean $populate If true (default), then update the layout first with new layout information from the $_REQUEST array
     */
    function handleSave ($populate = true)
    {
    	$GLOBALS [ 'log' ]->info ( get_class ( $this ) . "->handleSave()" ) ;

        if ($populate)
            $this->_populateFromRequest ( $this->_fielddefs ) ;

        $viewdefs = $this->_viewdefs ;
        $panels = each ( $this->_convertToCanonicalForm ( $this->_viewdefs [ 'panels' ] , $this->_fielddefs ) ) ;
        $viewdefs [ 'panels' ] = $panels [ 'value' ] ;
        $this->implementation->deploy ( array ( self::$variableMap [ $this->_view ] => $viewdefs ) ) ;
    }

}

?>