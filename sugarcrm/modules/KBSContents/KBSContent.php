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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

class KBSContent extends SugarBean {

    public $table_name = "kbscontents";
    public $object_name = "KBSContent";
    public $new_schema = true;
    public $module_dir = 'KBSContents';

    /**
     * {@inheritDoc}
     */
    public function save($check_notify = false)
    {
        if (!SugarBean::inOperation('saving_related')) {
            if ((!$this->id || $this->new_with_id) && empty($this->kbsdocument_id)) {
                if (!$this->id) {
                    $this->id = create_guid();
                    $this->new_with_id = true;
                }
                $doc = BeanFactory::getBean('KBSDocuments');
                $doc->new_with_id = true;
                $doc->id = create_guid();
                $doc->name = $this->name;
                $doc->save();
                $this->load_relationship('kbsdocuments_kbscontents');
                $this->kbsdocuments_kbscontents->add($doc);
            }
            if (!$this->active_rev) {
                $query = "UPDATE {$this->table_name}
                    set active_rev = 0
                    where active_rev = 1 and kbsdocument_id = {$this->db->quoted($this->kbsdocument_id)}";
                $this->db->query($query);
                $this->active_rev = 1;
            }
        }
        return parent::save($check_notify);
    }
}
