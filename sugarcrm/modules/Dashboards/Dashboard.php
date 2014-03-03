<?php
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

require_once('data/visibility/OwnerVisibility.php');

/**
 *  Dashboards is used to store dashboard configuration data.
 */
class Dashboard extends Basic 
{    
    public $table_name = "dashboards";
    public $module_name = 'Dashboards';
    public $module_dir = 'Dashboards';
    public $object_name = "Dashboard";
    
    public function __construct() 
    {
        parent::__construct();
        $this->addVisibilityStrategy("OwnerVisibility");
    }

    /**
     * This overrides the default retrieve function setting the default to encode to false
     */
    function retrieve($id='-1', $encode=false,$deleted=true)
    {
        $dashboard = parent::retrieve($id, false, $deleted);

        // Expand the metadata for processing.
        $metadata = json_decode($dashboard->metadata);

        // If we don't have a components in metadata for whatever reason, we're out, send back unchanged.
        if(!isset($metadata->components)) {
            return $dashboard;
        }

        $dirty = false;

        // Loop through the dashboard, drilling down to the dashlet level.
        foreach($metadata->components as $component_key => $component) {
            foreach($component->rows as $row_key => $row) {
                foreach($row as $item_key => $item) {

                    // No need to check access for a module if the module doesn't exist.
                    if (!isset($item->context->module)) {
                        continue;
                    }
                    $bean = BeanFactory::getBean($item->context->module);
                    // Check if this user has access to the module upon which this dashlet is based.
                    if (!SugarACL::checkAccess($item->context->module, 'access', array(), $bean->acltype)) {
                        // The user does not have access, remove the dashlet.
                        unset($metadata->components[$component_key]->rows[$row_key][$item_key]);

                        // Check if this row is now empty.
                        if(count($metadata->components[$component_key]->rows[$row_key]) == 0) {
                            // This row is now empty, remove it and mark the metadata as dirty.
                            unset($metadata->components[$component_key]->rows[$row_key]);
                            $dirty = true;
                        }
                    }
                }
            }
        }

        // Check if we've modified the metadata.
        if($dirty) {
            // Loop through the rows re-assigning sequential array keys for dashboard display.
            foreach($metadata->components as $key => $value) {
                $metadata->components[$key]->rows = array_values($metadata->components[$key]->rows);
            }
        }

        // Re-encode and save the metadata back to the dashboard object before returning it.
        $dashboard->metadata = json_encode($metadata);

        return $dashboard;
    }

    /**
     * This function fetches an array of dashboards for the current user
     *
     * 'view' is deprecated because it's reserved db word.
     * Some old API (before 7.2.0) can use 'view'.
     * Because of that API will use 'view' as 'view_name' if 'view_name' isn't present.
     * Returns all the dashboards available for the User given.
     *
     * Optionally you can pass the view in the $options to filter the
     * dashboards of a certain view.
     * For homepage the view is assumed empty.
     *
     * @param User $user The user that we want to get the dashboards from.
     * @param array $options A list of options such as: limit, offset and view.
     *
     * @return array The list of the User's dashboard and next offset.
     */
    public function getDashboardsForUser(User $user, array $options = array())
    {
        $order = !empty($options['order_by']) ? $options['order_by'] : 'date_entered desc';
        $from = "assigned_user_id = '".$this->db->quote($user->id)."' and dashboard_module ='".$this->db->quote($options['dashboard_module'])."'";
        if (isset($options['view']) && !isset($options['view_name'])) {
            $options['view_name'] = $options['view'];
        }
        if (!empty($options['view_name'])) {
            $from .= " and view_name =" . $this->db->quoted($options['view_name']);
        }
        $offset = !empty($options['offset']) ? (int)$options['offset'] : 0;
        $limit = !empty($options['limit']) ? (int)$options['limit'] : -1;
        $result = $this->get_list($order,$from,$offset,$limit,-1,0);
        $nextOffset = (count($result['list']) > 0 && count($result['list']) ==  $limit) ? ($offset + $limit) : -1;
        return array('records'=>$result['list'], 'next_offset'=>$nextOffset);
    }

    /**
     * This overrides the default save function setting assigned_user_id
     * @see SugarBean::save()
     *
     * 'view' is deprecated because it's reserved db word.
     * Some old API (before 7.2.0) can use 'view'.
     * Because of that API will use 'view' as 'view_name' if 'view_name' isn't present.
     */
    function save($check_notify = FALSE)
    {
        $this->assigned_user_id = $GLOBALS['current_user']->id;
        if (isset($this->view) && !isset($this->view_name)) {
            $this->view_name = $this->view;
        }
        // never send assignment notifications for dashboards
        return parent::save(false);
    }

    /**
     * 'view' is deprecated because it's reserved db word.
     * Some old API (before 7.2.0) can use 'view'.
     * Because of that API will return 'view' with the same value as 'view_name'.
     *
     * @param string $order_by
     * @param string $where
     * @param int    $row_offset
     * @param int    $limit
     * @param int    $max
     * @param int    $show_deleted
     * @param bool   $singleSelect
     * @param array  $select_fields
     *
     * @return array
     */
    public function get_list($order_by = "", $where = "", $row_offset = 0, $limit = -1, $max = -1, $show_deleted = 0, $singleSelect = false, $select_fields = array())
    {
        $result = parent::get_list($order_by, $where, $row_offset, $limit, $max, $show_deleted, $singleSelect, $select_fields);
        if (!empty($result['list'])) {
            foreach ($result['list'] as $dashboard) {
                $dashboard->view = $dashboard->view_name;
            }
        }
        return $result;
    }
}
