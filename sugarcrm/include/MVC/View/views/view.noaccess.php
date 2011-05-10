<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/

class ViewNoaccess extends SugarView
{
	public $type = 'noaccess';
	
	/**
	 * @see SugarView::display()
	 */
	public function display()
	{
		echo '<p class="error">Warning: You do not have permission to access this module.</p>';
 	}
}
