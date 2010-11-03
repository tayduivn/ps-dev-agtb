{capture name=getPhone assign=phone}{sugar_fetch object=$parentFieldArray key=$col}{/capture}
{capture name=getBeanId assign=bean_id}{sugar_fetch object=$parentFieldArray key=ID}{/capture}
{assign var=this_module value=$smarty.request.module}
{assign var=this_id value=$smarty.request.record}
{fonality_phone value=$phone this_module=$this_module this_id=$this_id contact_id=$bean_id}