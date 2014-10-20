<!---------------  START WORKFLOW SHOWCASE ------------>
<form action="index.php?module=ProcessMaker&action=routeCase" id="showCaseForm" method="POST">
    <input type="hidden" name="cas_id" id="cas_id" value="{$cas_id}"/>
    <input type="hidden" name="cas_index" id="cas_index" value="{$cas_index}"/>
    <input type="hidden" name="cas_current_user_id" id="cas_index" value="{$cas_current_user_id}"/>
    <input type="hidden" name="act_adhoc_behavior" id="cas_index" value="{$act_adhoc_behavior}"/>
    <input type="hidden" name="act_adhoc_assignment" id="cas_index" value="{$act_adhoc_assignment}"/>
    {foreach from=$customButtons key='key' item='item'}
        <input name="{$item.name}" type="{$item.type}" value={$item.value} onclick="{$item.onclick}">
    {/foreach}    
<!---------------  END WORKFLOW SHOWCASE ------------>