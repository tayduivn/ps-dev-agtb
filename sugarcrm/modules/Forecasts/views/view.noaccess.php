<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/

class ForecastsViewNoaccess extends ViewNoaccess
{
	public function display()
	{
        global $mod_strings;
		echo "<p class='error'>{$mod_strings['LBL_UNAUTH_FORECASTS']}</p>";
 	}
}
