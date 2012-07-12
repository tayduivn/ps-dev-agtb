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
 * Portal visibility class replaces the team security restrictions for portal users
 * For non-portal users this class will not modify the query in any way.
 */
class SupportPortalVisibility extends SugarVisibility
{
    protected $wherePart = '';

    /**
     * This function is here so we can put all the rules in one local section and just have it return the part of the query for that particular type
     * @param $query string The query string (probably shouldn't need to modify it here, but just in case)
     * @param $queryType string Either 'from' or 'where' to match the two types we understand right now.
     * @return string What to append to the query
     */
    protected function addVisibilityPortal($query, $queryType) 
    {
        if ( empty($_SESSION['type']) || $_SESSION['type'] != 'support_portal' ) {
            $GLOBALS['log']->error("Not a portal user, but running through the portal visibility class.");
            return;
        }
        if ($this->bean->disable_row_level_security) {
            $GLOBALS['log']->debug("No portal security applied to module (row-level security disabled): ".$this->bean->module_dir);
            return;
        }


        $table_alias = $this->getOption('table_alias');
        if(empty($table_alias)) {
            $table_alias = $this->bean->table_name;
        }

        if ( !empty($_SESSION['account_ids']) ) {
            $accountIn = "('".implode("','",$_SESSION['account_ids'])."')";
        } else {
            // No accounts
            $accountIn = '()';
        }
        // $_SESSION['contact_id']
        // $_SESSION['account_ids']

        $queryPart = '';

        // The Portal Rules Of Visibility:
        switch ($this->bean->module_dir) {
            case 'Contacts':
                // Contacts: Any contact related to the account list
                if ( $queryType == 'from' ) {
                    $this->bean->load_relationship('accounts');
                    $queryPart = $this->bean->accounts->getJoin(array('join_table_alias'=>'accounts_pv'))." AND accounts_pv.id IN $accountIn ";
                }
                break;
            case 'Accounts':
                // Accounts: Any account in the account list
                if ( $queryType == 'where' ) {
                    $queryPart = " $table_alias.id IN $accountIn ";
                }
                break;
            case 'Bugs':
                // Bugs: Any bug that has the portal_viewable flag set to true
                if ( $queryType == 'where' ) {
                //BEGIN SUGARCRM flav=ent ONLY
                    $queryPart = " $table_alias.portal_viewable = 1 ";
                //END SUGARCRM flav=ent ONLY
                }

                break;
            case 'KBDocuments':
                // KBDocuments: Any KBDocument where is_external_article = 1 AND ( exp_date is empty or > today ) AND status_id = Published
                if ( $queryType == 'where' ) { 
                    $queryPart = " {$table_alias}.is_external_article = 1 "
                        ."AND ( {$table_alias}.exp_date IS NULL OR {$table_alias}.exp_date = '' OR {$table_alias}.exp_date > NOW() ) "
                        ."AND ( {$table_alias}.active_date IS NULL OR {$table_alias}.active_date = '' OR {$table_alias}.active_date < NOW() ) "
                        ."AND {$table_alias}.status_id = 'Published' ";
                }

                break;
            case 'Notes':
                // Notes: Notes that are connected to a Case or a Bug that is connected to one of our Accounts and has the portal_flag set to true
                if ( $queryType == 'from' ) {
                    $this->bean->load_relationship('cases');
                    
                    $caseBean = BeanFactory::newBean('Cases');
                    $caseBean->load_relationship('accounts');

                    $queryPart = $this->bean->cases->getJoin(array('join_table_alias'=>'cases_pv','join_type'=>' LEFT JOIN '));
                    //BEGIN SUGARCRM flav=ent ONLY
                    $queryPart .= " AND cases_pv.portal_viewable = 1 ";
                    //ENd SUGARCRM flav=ent ONLY
                    $queryPart .= " ".$caseBean->accounts->getJoin(array('join_table_alias'=>'accounts_cases_pv','right_join_table_alias'=>'cases_pv','join_type' => ' LEFT JOIN '))." AND accounts_cases_pv.id IN $accountIn ";

                    $this->bean->load_relationship('bugs');
                    
                    $bugBean = BeanFactory::newBean('Bugs');
                    $bugBean->load_relationship('accounts');

                    $queryPart .= " ".$this->bean->bugs->getJoin(array('join_table_alias'=>'bugs_pv','join_type'=>' LEFT JOIN '));
                    //BEGIN SUGARCRM flav=ent ONLY
                    $queryPart .= " AND bugs_pv.portal_viewable = 1 ";
                    //ENd SUGARCRM flav=ent ONLY

                } else if ( $queryType == 'where' ) {
                    $queryPart = " {$table_alias}.portal_flag = 1 AND ( bugs_pv.id IS NOT NULL OR accounts_cases_pv.id IS NOT NULL ) ";
                }
                break;
            case 'Cases':
                // Cases: Any case that has the portal_viewable flag set to true and is related to the account list
            default:
                // Other: Same as cases, if there is no portal_viewable (or portal_viewable_c) field, it is not portal accessible
                $additionalPart = '';
                if ( $this->bean->module_dir == 'Cases' ) {
                    $portalEnabled = true;
                    $linkName = 'accounts';
                    //BEGIN SUGARCRM flav=ent ONLY
                    $wherePart = " $table_alias.portal_viewable = 1 ";
                    //END SUGARCRM flav=ent ONLY
                } else {
                    $portalEnabled = false;
                    // TODO: Currently custom modules are not supported in portal, but if they were, 
                    // it'd look a little something like this:
                    /*
                    // We have to find a portal_viewable field before this module will be viewable in the portal
                    if ( isset($this->bean->field_defs['portal_viewable']) ) {
                        $wherePart = " $table_alias.portal_viewable = 1 ";
                        $portalEnabled = true;
                    } else if ( isset($this->bean->field_defs['portal_viewable_c']) ) {
                        // Custom portal_viewable field... the table name is a bit of a mystery.
                        $wherePart = " {$table_alias}_cstm.portal_viewable_c = 1 ";
                        $portalEnabled = true;
                    }
                    if ( $portalEnabled && $queryType == 'from' ) {
                        // This is expensive, we need to find the link to the Accounts module that this random module uses
                        if ( isset($this->bean->field_defs['accounts']) ) {
                            $linkName = 'accounts';
                        } else {
                            // Couldn't find a link by that name
                            $portalEnabled = false;
                        }
                    }
                    */
                }
                
                if ( $portalEnabled ) {
                    if ( $queryType == 'from' ) {
                        $this->bean->load_relationship($linkName);
                        $queryPart = $this->bean->$linkName->getJoin(array('join_table_alias'=>$linkName.'_pv'))." AND {$linkName}_pv.id IN $accountIn ";
                    } else if ( $queryType == 'where' ) {
                        $queryPart = $wherePart;
                    }
                } else {
                    // This module is not a default portal module, nor can I figure out how the portal visibility for this module works, so I am here to make sure they don't see anything.
                    if ( $queryType == 'where' ) {
                        $queryPart = ' 1=0 ';
                    }
                    
                }
                break;
        }
        
        return $queryPart;
    }

    public function addVisibilityFrom(&$query)
    {
        $query .= $this->addVisibilityPortal($query,'from');
        return;
    }

    public function addVisibilityWhere(&$query)
    {
        $queryPart = $this->addVisibilityPortal($query,'where');
        if ( !empty($query) && !empty($queryPart) ) {
            $query .= " AND ".$queryPart;
        } else if (!empty($queryPart)) {
            $query .= $queryPart;
        }
        return;
    }

}