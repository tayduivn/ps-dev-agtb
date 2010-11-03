<?php
$viewdefs ['Contracts'] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'javascript' => '<script type="text/javascript" language="javascript">
	function setvalue(source)  {ldelim} 
		src= new String(source.value);
		target=new String(source.form.name.value);

		if (target.length == 0)  {ldelim} 
			lastindex=src.lastIndexOf("\\"");
			if (lastindex == -1)  {ldelim} 
				lastindex=src.lastIndexOf("\\\\\\"");
			 {rdelim}  
			if (lastindex == -1)  {ldelim} 
				source.form.name.value=src;
				source.form.escaped_name.value = src;
			 {rdelim}  else  {ldelim} 
				source.form.name.value=src.substr(++lastindex, src.length);
				source.form.escaped_name.value = src.substr(lastindex, src.length);
			 {rdelim} 	
		 {rdelim} 			
	 {rdelim} 

	function set_expiration_notice_values(form)  {ldelim} 
		if (form.expiration_notice_flag.checked)  {ldelim} 
			form.expiration_notice_flag.value = "on";
			form.expiration_notice_date.value = "";
			form.expiration_notice_time.value = "";
			form.expiration_notice_date.readonly = true;
			form.expiration_notice_time.readonly = true;
			if(typeof(form.due_meridiem) != \'undefined\')  {ldelim} 
				form.due_meridiem.disabled = true;
			 {rdelim} 
			
		 {rdelim}  else  {ldelim} 
			form.expiration_notice_flag.value="off";
			form.expiration_notice_date.readOnly = false;
			form.expiration_notice_time.readOnly = false;
			
			if(typeof(form.due_meridiem) != \'undefined\')  {ldelim} 
				form.due_meridiem.disabled = false;
			 {rdelim} 
			
		 {rdelim} 
	 {rdelim} 
</script>',
    ),
    'panels' => 
    array (
      '
		  ' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'comment' => 'The name of the contract',
            'label' => 'LBL_CONTRACT_NAME',
          ),
          1 => 
          array (
            'name' => 'start_date',
            'displayParams' => 
            array (
              'showFormats' => true,
            ),
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'agreement_type_c',
            'studio' => 'visible',
            'label' => 'LBL_AGREEMENT_TYPE',
          ),
          1 => 
          array (
            'name' => 'end_date',
            'displayParams' => 
            array (
              'showFormats' => true,
            ),
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'status',
            'comment' => 'The contract status',
            'label' => 'LBL_STATUS',
          ),
          1 => 
          array (
            'name' => 'contract_term_c',
            'studio' => 'visible',
            'label' => 'LBL_CONTRACT_TERM',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'account_name',
            'label' => 'LBL_ACCOUNT_NAME',
          ),
          1 => 
          array (
            'name' => 'currency_id',
            'label' => 'LBL_CURRENCY',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'opportunity_name',
            'label' => 'LBL_OPPORTUNITY_NAME',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'required' => true,
            ),
          ),
          1 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'displayParams' => 
            array (
              'rows' => 10,
              'cols' => 90,
            ),
          ),
        ),
      ),
    ),
  ),
);
?>
