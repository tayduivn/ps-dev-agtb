<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

class ForecastManagerWorksheetHooks
{
    /**
     * This checks to see if the only thing that has changed is the quota, if it is, then don't update the date
     * modified
     *
     * @param ForecastManagerWorksheet $worksheet       The Bean
     * @param string $event                             Which event is being fired
     * @param array $params                             Extra Params
     */
    public static function draftRecordQuotaOnlyCheck(ForecastManagerWorksheet $worksheet, $event, $params = array())
    {
        // this should only run on before_save and when the worksheet is a draft record
        if ($event == 'before_save' && $worksheet->draft == 1) {
            $mm = new MetadataManager($GLOBALS['current_user']);
            $views = $mm->getModuleViews($worksheet->module_name);

            $fields = $views['list']['meta']['panels'][0]['fields'];

            $onlyQuotaChanged = true;

            foreach ($fields as $field) {
                if ($field['type'] == 'currency' && preg_match('#\.[\d]{6}$#', $worksheet->$field['name']) === 0) {
                    $worksheet->$field['name'] = SugarMath::init($worksheet->$field['name'], 6)->result();
                }

                if ($worksheet->fetched_row[$field['name']] !== $worksheet->$field['name']) {
                    if ($field['name'] !== 'quota') {
                        $onlyQuotaChanged = false;
                        break;
                    }
                }
            }

            if ($onlyQuotaChanged === true) {
                $worksheet->update_date_modified = false;
            }
        }
    }
}
