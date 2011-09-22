<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

 
require_once 'modules/ProductBundleNotes/ProductBundleNote.php';

class SugarTestProductBundleNoteUtilities
{
    private static $_createdProductBundleNotes = array();

    private function __construct() {}

    public static function createProductBundleNote($id = '') 
    {
        $time = mt_rand();
        $name = 'SugarProductBundleNote';
        $productBundleNote = new ProductBundleNote();
        $productBundleNote->name = $name . $time;

        if(!empty($id))
        {
            $productBundleNote->new_with_id = true;
            $productBundleNote->id = $id;
        }
        $productBundleNote->save();
        self::$_createdProductBundleNotes[] = $productBundleNote;
        return $productBundleNote;
    }

    public static function setCreatedProductBundleNote($productBundleNote_ids) {
        foreach($productBundleNote_ids as $productBundleNote_id) {
            $productBundleNote = new ProductBundleNote();
            $productBundleNote->id = $productBundleNote_id;
            self::$_createdProductBundleNotes[] = $productBundleNote;
        } // foreach
    } // fn
    
    public static function removeAllCreatedProductBundleNotes() 
    {
        $productBundleNote_ids = self::getCreatedProductBundleNoteIds();
        $GLOBALS['db']->query('DELETE FROM product_bundle_notes WHERE id IN (\'' . implode("', '", $productBundleNote_ids) . '\')');
    }
        
    public static function getCreatedProductBundleNoteIds() 
    {
        $productBundleNote_ids = array();
        foreach (self::$_createdProductBundleNotes as $productBundleNote) {
            $productBundleNote_ids[] = $productBundleNote->id;
        }
        return $productBundleNote_ids;
    }
}
?>