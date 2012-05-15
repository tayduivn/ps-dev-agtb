<?php
//FILE SUGARCRM flav=pro ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 ********************************************************************************/

class OpportunityLine extends SugarBean
{
    // Stored fields
    var $id;
    var $product_id;
    var $product_category_id;
    var $experts;
    var $product_owner_id;
    var $table_name = "opportunity_line";
    var $module_dir = 'OpportunityLines';
    var $object_name = "OpportunityLine";

    function getExperts()
    {
        $query = "SELECT " . $this->db->convert("p.category_id", "IFNULL", array("pt.category_id")) . " category_id
                FROM products p
                LEFT JOIN product_templates pt on p.product_template_id = pt.id
                WHERE p.id = '$this->product_id'
                    AND p.deleted = 0";

        $result = $this->db->query($query,true," Error getting product category id: ");

        $row = $this->db->fetchByAssoc($result);

        if($row != null)
        {
            $this->product_category_id = $row['category_id'];
            $this->getProductOwners($this->product_category_id);
        }
        else
        {
            $this->experts = '';
        }
    }

    function getProductOwners($category_id)
    {
        $query = "SELECT assigned_user_id, parent_id
                    FROM product_categories pc
                    WHERE id = '$category_id'";

        $result = $this->db->query($query, true, "Error getting product category additional fields: ");

        $row = $this->db->fetchByAssoc($result);

        if ($row != null)
        {
            $this->experts[] = $row['assigned_user_id'];
            $this->getProductOwners($row['parent_id']);
            $this->product_owner_id = $row['id'];
        }
        else
        {
            $this->product_owner_id = '';
        }
    }
}
