<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
********************************************************************************/

$dictionary['tag_bean_rel'] = array(
    'table' => 'tag_bean_rel',
    'indices' => array(),
    'relationships' => array(),
    'fields' => array(
        array(
            'name' => 'id',
            'type' => 'id',
            'required' => true,
        ),
        array(
            'name' => 'tag_id',
            'type' => 'id',
            'required' => true,
        ),
        array(
            'name' => 'bean_id',
            'type' => 'id',
            'required' => true,
        ),
        array(
            'name' => 'bean_module',
            'type' => 'varchar',
            'len'  => 100,
        ),
        array(
            'name' => 'date_modified',
            'type' => 'datetime',
        ),
        array(
            'name' => 'deleted',
            'type' => 'bool',
            'default' => '0',
        ),
    ),
    'indices' => array(
        array(
            'name'   => 'tags_bean_relpk',
            'type'   => 'primary',
            'fields' => array('id'),
        ),
        array(
            'name'	 => 'idx_tagsrel_tagid_beanid',
            'type'	 => 'index',
            'fields' => array('tag_id','bean_id'),
        ),
    ),
);
