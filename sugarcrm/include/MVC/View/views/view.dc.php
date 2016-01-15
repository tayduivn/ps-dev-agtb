<?php
//FILE SUGARCRM flav=int ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once('include/MVC/View/SugarView.php');
require_once('include/DashletContainer/DCFactory.php');

class ViewDC extends SugarView
{
    /**
     * @see SugarView::display()
     */
    public function display()
    {
        if (!isset($this->bean) || empty($this->bean->id))
            return;
        
        $path = 'modules/' . $this->module . '/metadata/subdashdefs.php';
        $dc = DCFactory::getContainer($path, 'DCList');
        $dc->setFocusBean($this->bean);
        $code = $dc->getLayout();
        foreach ($code['jsfiles'] as $script)
            echo "<script type='text/javascript' src='$script'></script>";
        
        echo $code['html'];
    }
}
