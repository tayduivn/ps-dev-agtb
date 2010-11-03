<div style='width: 500px'>
<form action='index.php' id='configure_{$id}' method='post' onSubmit='SUGAR.mySugar.setChooser(); return SUGAR.dashlets.postForm("configure_{$id}", SUGAR.mySugar.uncoverPage);'>
<input type='hidden' name='id' value='{$id}'>
<input type='hidden' name='module' value='Home'>
<input type='hidden' name='action' value='ConfigureDashlet'>
<input type='hidden' name='configure' value='true'>
<input type='hidden' name='to_pdf' value='true'>
<input type='hidden' id='displayColumnsDef' name='displayColumnsDef' value=''>
<input type='hidden' id='hideTabsDef' name='hideTabsDef' value=''>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tabForm">
	<tr>
        <td class='dataLabel' colspan='4' align='left'>
        	<h2>{$strings.general}</h2>
        </td>
    </tr>
    <tr>
	    <td class='dataLabel'>
		    {$strings.title}
        </td>
        <td class='dataField' colspan='3'>
            <input type='text' name='dashletTitle' value='{$dashletTitle}'>
        </td>
	</tr>
    <tr>
	    <td class='dataLabel'>
		    {$strings.displayRows}
        </td>
        <td class='dataField' colspan='3'>
            <select name='displayRows'>
				{html_options values=$displayRowOptions output=$displayRowOptions selected=$displayRowSelect}
           	</select>
        </td>
    </tr>
    <tr>
        <td colspan='4' align='center'>
        	<table border='0' cellpadding='0' cellspacing='0'>
        	<tr><td>
			    {$columnChooser}
		    </td>
		    </tr></table>
	    </td>
	</tr>
    <tr>
	    <td class='dataLabel'>

        </td>
        <td class='dataField'>
            {$strings.hoursStart}
        </td>

	    <td class='dataLabel'>
            {$strings.hoursEnd}
        </td>
        <td class='dataField'>

        </td>
    </tr>
    <tr>

	    <td class='dataLabel'>
            {$strings.mon}
        </td>
        <td class='dataField'>
            <input type='text' size='7' maxlength='7' name='monOpen' value='{$monOpen}'>
        </td>

	    <td class='dataLabel'>
            <input type='text'  size='7' maxlength='7' name='monClose' value='{$monClose}'>
        </td>
        <td class='dataField'>

        </td>
    </tr>
    <tr>

	    <td class='dataLabel'>
            {$strings.tue}
        </td>
        <td class='dataField'>
            <input type='text'  size='7' maxlength='7' name='tueOpen' value='{$tueOpen}'>
        </td>

	    <td class='dataLabel'>
            <input type='text'  size='7' maxlength='7' name='tueClose' value='{$tueClose}'>
        </td>
        <td class='dataField'>

        </td>
    </tr><tr>

	    <td class='dataLabel'>
            {$strings.wed}
        </td>
        <td class='dataField'>
            <input type='text'  size='7' maxlength='7' name='wedOpen' value='{$wedOpen}'>
        </td>

	    <td class='dataLabel'>
            <input type='text'  size='7' maxlength='7' name='wedClose' value='{$wedClose}'>
        </td>
        <td class='dataField'>

        </td>
    </tr>
    <tr>

	    <td class='dataLabel'>
            {$strings.thu}
        </td>
        <td class='dataField'>
            <input type='text'  size='7' maxlength='7' name='thuOpen' value='{$thuOpen}'>
        </td>

	    <td class='dataLabel'>
            <input type='text'  size='7' maxlength='7' name='thuClose' value='{$thuClose}'>
        </td>
        <td class='dataField'>

        </td>
    </tr>
    <tr>

	    <td class='dataLabel'>
            {$strings.fri}
        </td>
        <td class='dataField'>
            <input type='text'  size='7' maxlength='7' name='friOpen' value='{$friOpen}'>
        </td>

	    <td class='dataLabel'>
            <input type='text'  size='7' maxlength='7' name='friClose' value='{$friClose}'>
        </td>
        <td class='dataField'>

        </td>
    </tr>
    <tr>

	    <td class='dataLabel'>
            {$strings.sat}
        </td>
        <td class='dataField'>
            <input type='text'  size='7' maxlength='7' name='satOpen' value='{$satOpen}'>
        </td>

	    <td class='dataLabel'>
            <input type='text'  size='7' maxlength='7' name='satClose' value='{$satClose}'>
        </td>
        <td class='dataField'>

        </td>
    </tr>
    <tr>

	    <td class='dataLabel'>
            {$strings.sun}
        </td>
        <td class='dataField'>
            <input type='text'  size='7' maxlength='7' name='sunOpen' value='{$sunOpen}'>
        </td>

	    <td class='dataLabel'>
            <input type='text'  size='7' maxlength='7' name='sunClose' value='{$sunClose}'>
        </td>
        <td class='dataField'>

        </td>
    </tr>
    <tr>
    {foreach name=ClosedValues from=$closedValues key=name item=params}
        <td class='dataLabel' valign='top'>
            {$params.label}
        </td>
        <td class='dataField' valign='top' style='padding-bottom: 5px'>
            {$params.input}
        </td>
        </tr><tr>
        {/foreach}
    </tr>
	<tr>
        <td class='dataLabel' colspan='4' align='left'>
	        <br>
        	<h2>{$strings.filters}</h2>
        </td>
    </tr>
    <tr>
	    <td class='dataLabel'>
            {$strings.myItems}
        </td>
        <td class='dataField'>
            <input type='checkbox' {if $myItemsOnly == 'true'}checked{/if} name='myItemsOnly' value='true'>
        </td>

	    <td class='dataLabel'>
            {$strings.businessHours}
        </td>
        <td class='dataField'>
            <input type='checkbox' {if $businessHours == 'true'}checked{/if} name='businessHours' value='true'>
        </td>
    </tr>
    <tr>
    {foreach name=searchIteration from=$searchFields key=name item=params}
        <td class='dataLabel' valign='top'>
            {$params.label}
        </td>
        <td class='dataField' valign='top' style='padding-bottom: 5px'>
            {$params.input}
        </td>
        {if ($smarty.foreach.searchIteration.iteration is even) and $smarty.foreach.searchIteration.iteration != $smarty.foreach.searchIteration.last}
        </tr><tr>
        {/if}
    {/foreach}
    </tr>
    <tr>
	    <td colspan='4' align='left'>
	        <input type='submit' class='button' value='{$strings.save}'>
	    </td>
	</tr>
</table>
</form>
</div>