<?php
if (! defined ( 'sugarEntry' ) || ! sugarEntry) die ( 'Not A Valid Entry Point' ) ;
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once ('modules/DynamicFields/templates/Fields/TemplateField.php') ;
require_once ('modules/DynamicFields/templates/Fields/TemplateAddressCountry.php') ;

class TemplateAddress extends TemplateField
{
    function save ($df)
    {
        // Bug 58560 - Set the group name since addresses are part of a group
        $this->group = $df->getDBName($this->name);
        
        require_once 'modules/ModuleBuilder/parsers/parser.label.php' ;
        $parser = new ParserLabel ( $df->getModuleName() , $df->getPackageName() ) ;
        
        // Clean up the labels so they more accurately reflect the actual field
        if (!empty($this->label_value)) {
            $labelValue = $this->label_value;
        } else {
            $labelValue = empty($_REQUEST['labelValue']) ? '' : $_REQUEST['labelValue'];
        }
        
        // If there is a label to use, space it here for use below
        if (!empty($labelValue)) {
            $labelValue .= ' ';
        }
        
        // To keep consistency with OOTB address groups, add Street to the fields
        foreach ( array ( 'Street', 'City' , 'State' , 'PostalCode' , 'Country' ) as $addressFieldName )
        {
            $systemLabel = strtoupper( "LBL_" . $this->name . '_' . $addressFieldName );
            // Use the entered label value as a prefix instead of the field name
            $parser->handleSave ( array( "label_" . $systemLabel => $labelValue . $addressFieldName ) , $GLOBALS [ 'current_language' ] ) ;
            $addressField = new TemplateField ( ) ;
            $addressField->len = ($addressFieldName == 'PostalCode') ? 20 : 100 ;
            $addressField->name = $this->name . '_' . strtolower ( $addressFieldName ) ;
            $addressField->label = $addressField->vname = $systemLabel ;
            // Bug 58560 - Add the group to this field so it gets written to the custom vardefs
            $addressField->group = $this->group;
            
            // Maintain unified search setting for 'Street'
            $addressField->supports_unified_search = $addressField == 'Street';
            
            $addressField->save ( $df ) ;
        }
    }
}

?>
