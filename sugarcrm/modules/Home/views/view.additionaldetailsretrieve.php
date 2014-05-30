<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
require_once('include/MVC/View/SugarView.php');

class HomeViewAdditionaldetailsretrieve extends SugarView
{
 	public function display()
 	{
        global $beanList, $beanFiles, $current_user, $app_strings, $app_list_strings;

        $moduleDir = empty($_REQUEST['bean']) ? '' : $_REQUEST['bean'];
        $beanName = empty($beanList[$moduleDir]) ? '' : $beanList[$moduleDir];
        $id = empty($_REQUEST['id']) ? '' : $_REQUEST['id'];

        // Bug 40216 - Add support for a custom additionalDetails.php file
        $additionalDetailsFile = $this->getAdditionalDetailsMetadataFile($moduleDir);

        if(empty($id) || empty($additionalDetailsFile) ) {
                echo 'bad data';
                die();
        }

        require_once($additionalDetailsFile);
        $adFunction = 'additionalDetails' . $beanName;

        if(function_exists($adFunction)) { // does the additional details function exist
            $json = getJSONobj();
            $bean = BeanFactory::getBean($moduleDir, $id);

            //bug38901 - shows dropdown list label instead of database value
            foreach ($bean->field_name_map as $field => $value) {
                if ($value['type'] == 'enum' &&
                    !empty($value['options']) &&
                    !empty($app_list_strings[$value['options']]) &&
                    isset($app_list_strings[$value['options']][$bean->$field])
                ) {
                    $bean->$field = $app_list_strings[$value['options']][$bean->$field];
                }
            }

            //BEGIN SUGARCRM flav=pro ONLY
            $bean->ACLFilterFields();
            //END SUGARCRM flav=pro ONLY
            $arr = array_change_key_case($bean->toArray(), CASE_UPPER);
            $results = $adFunction($arr);

            $retArray['body'] = str_replace(array("\rn", "\r", "\n"), array('','','<br />'), $results['string']);
            $retArray['caption'] = "<div style='float:left'>{$app_strings['LBL_ADDITIONAL_DETAILS']}</div><div style='float: right'>";

            if (!empty($_REQUEST['show_buttons'])) {
                if ($bean->ACLAccess('EditView')) {
                    $editImg = SugarThemeRegistry::current()->getImageURL('edit_inline.png', false);
                    $results['editLink'] = $this->buildButtonLink($results['editLink']);
                    $retArray['caption'] .= <<<EOC
<a style="text-decoration:none;" title="{$GLOBALS['app_strings']['LBL_EDIT_BUTTON']}" href="{$results['editLink']}">
    <img border=0 src="$editImg">
</a>
EOC;
                }
                if ($bean->ACLAccess('DetailView')) {
                    $detailImg = SugarThemeRegistry::current()->getImageURL('view_inline.png', false);
                    $results['viewLink'] = $this->buildButtonLink($results['viewLink']);
                    $retArray['caption'] .= <<<EOC
<a style="text-decoration:none;" title="{$GLOBALS['app_strings']['LBL_VIEW_BUTTON']}" href="{$results['viewLink']}">
    <img border=0 src="$detailImg" style="margin-left:2px;">
</a>
EOC;
                }
                $closeImg = SugarThemeRegistry::current()->getImageURL('close.png', false);
                $retArray['caption'] .= <<<EOC
<a title="{$GLOBALS['app_strings']['LBL_ADDITIONAL_DETAILS_CLOSE_TITLE']}" href="javascript:SUGAR.util.closeStaticAdditionalDetails();">
    <img border=0 src="$closeImg" style="margin-left:2px;">
</a>
EOC;
            }
            $retArray['caption'] .= "";
            $retArray['width'] = (empty($results['width']) ? '300' : $results['width']);
            echo 'result = ' . $json->encode($retArray);
        }
    }

    /**
     * Builds an appropriate Sidecar or BWC href attribute for the additional
     * details buttons, using the link supplied from the additional details
     * module metadata.
     *
     * @private
     * @param string $link (optional) The link from additional details module
     *   metadata. The function returns an empty string if none is supplied.
     * @return string The href attribute used for the button.
     */
    private function buildButtonLink($link = '')
    {
        if (preg_match('/module=([^&]+)/', $link, $matches) && !isModuleBWC($matches[1])) {
            parse_str(parse_url($link, PHP_URL_QUERY), $params);
            $script = navigateToSidecar(
                buildSidecarRoute($params['module'], $params['record'], translateToSidecarAction($params['action']))
            );
            $link = "javascript:$script;";
        }

        return $link;
    }

    protected function getAdditionalDetailsMetadataFile($moduleName)
    {
        return SugarAutoLoader::existingCustomOne('modules/' . $moduleName . '/metadata/additionalDetails.php');
    }
}
