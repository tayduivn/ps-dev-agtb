<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


class SugarTestProductTemplatesUtilities
{
    protected static $createdProductTemplates = array();

    /**
     * @param string $id
     * @param array $fields         A key value pair to set field values on the created product template
     * @return ProductTemplate
     */
    public static function createProductTemplate($id = '', $fields = array())
    {
        $time = mt_rand();
        $name = 'SugarProductTemplate';
        /* @var $product_template ProductTemplate */
        $product_template = BeanFactory::getBean('ProductTemplates');
        $product_template->name = $name . $time;
        if (!empty($id)) {
            $product_template->new_with_id = true;
            $product_template->id = $id;
        }
        foreach ($fields as $key => $value) {
            $product_template->$key = $value;
        }
        $product_template->save();
        self::$createdProductTemplates[] = $product_template->id;
        return $product_template;
    }

    public static function setCreatedProductTemplate($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        foreach ($ids as $id) {
            self::$createdProductTemplates[] = $id;
        }
    }

    public static function removeAllCreatedProductTemplate()
    {
        $db = DBManagerFactory::getInstance();
        $db->query(
            'DELETE FROM product_categories WHERE id IN ("' . implode(
                "', '",
                self::getCreatedProductTemplateIds()
            ) . '")'
        );
    }

    public static function getCreatedProductTemplateIds()
    {
        return self::$createdProductTemplates;
    }
}
