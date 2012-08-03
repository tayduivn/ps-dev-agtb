<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
$buttons = array('EDIT', 'DUPLICATE', 'DELETE', 'FIND_DUPLICATES');
if(ACLController::checkAccess('KBDocuments', 'edit', true))
{
    array_push($buttons, array('customCode'=>'<input title="{$MOD.LBL_CREATE_KB_DOCUMENT}" accessKey="M" class="button" onclick="this.form.return_module.value=\'Cases\'; this.form.return_action.value=\'DetailView\';this.form.action.value=\'EditView\';this.form.module.value=\'KBDocuments\';" type="submit" name="button" value="{$MOD.LBL_CREATE_KB_DOCUMENT}">',
        //Bug#51778: The custom code will be replaced with sugar_html. customCode will be deplicated.
        'sugar_html' => array(
            'type' => 'submit',
            'value' => '{$MOD.LBL_CREATE_KB_DOCUMENT}',
            'htmlOptions' => array(
                'title' => '{$MOD.LBL_CREATE_KB_DOCUMENT}',
                'accessKey' => 'M',
                'class' => 'button',
                'onclick' => 'this.form.return_module.value=\'Cases\'; this.form.return_action.value=\'DetailView\';this.form.action.value=\'EditView\';this.form.module.value=\'KBDocuments\';',
                'name' => 'button',
            ),
        ),
    ));
}
$viewdefs['Cases']['DetailView'] = array(

'templateMeta' => array('form' => array('buttons' =>$buttons),

                        'maxColumns' => '2',
                        'widths' => array(
                                        array('label' => '10', 'field' => '30'),
                                        array('label' => '10', 'field' => '30')
                                        ),
                        ),
'panels' =>array (
  'lbl_case_information'=>array(
	  array (
	    array('name' => 'case_number', 'label' => 'LBL_CASE_NUMBER'),
	    'priority'
	  ),

	  array (
	    'status',
	    'account_name',
	  ),
	  array (
	      'type',
	  ),

	  array (

	    array (
	      'name' => 'name',
	      'label' => 'LBL_SUBJECT',
	    ),
	  ),

	  array (
	    'description',
	  ),

	  array (
	    'resolution',
	  ),

	  //BEGIN SUGARCRM flav=ent ONLY
	  array (
	     array('name'=>'portal_viewable',
			   'label' => 'LBL_SHOW_IN_PORTAL',
		       'hideIf' => 'empty($PORTAL_ENABLED)',
		      ),
	  ),
	  //END SUGARCRM flav=ent ONLY
	),

	'LBL_PANEL_ASSIGNMENT' => array(
        array (
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
          array (
            'name' => 'date_modified',
            'label' => 'LBL_DATE_MODIFIED',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
          ),
        ),
        array (
		  //BEGIN SUGARCRM flav=pro ONLY
		  'team_name',
		  //END SUGARCRM flav=pro ONLY
          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
          ),
        ),
	),
)



);
?>