<?php

/**
 * @author jwhitcraft
 * @project MoofCart
 * @tasknum 3
 * Logic Hook to move custom fields from the product template
 * to the product
 */


/**
 * @author jbartek
 * @project MoofCart
 * @tasknum 82
 * Added price_format_c and percentage_c to the fields array
 * to the product
 */

class updateCustomFieldsFromTemplate {
    function update(&$bean, $event, $arguments) {
        // don't run unless the event is before_save
        $GLOBALS['log']->info("JWHITCRAFT - Product Template_id: " . $bean->product_template_id);
        if ($event == "before_save") {
            // create a new producttemplate object and load it with the product template
            // that is assigned to the template
            $template = new ProductTemplate();
            $template->retrieve($bean->product_template_id);

            // this is the list of fields that we need to copy over.
            // since they are the same in both beans, just one field
            // is needed
            $fields = array(
		'price_format_c',
		'percentage_c',
		);

            // loop though the fields and copy them from the template to the bean
            foreach ($fields as $field) {
                $bean->$field = $template->$field;
            }

            // return true just for the hell of it.
            return true;
        }

    }
}

