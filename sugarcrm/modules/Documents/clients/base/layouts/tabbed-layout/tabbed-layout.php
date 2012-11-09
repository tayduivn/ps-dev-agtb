<?php
$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view' => 'activitystream', 'label' => 'Activity Stream'));
$layout->push(array('layout' => 'list-cluster', 'label' => 'Document Revisions', 'context' => array('link' => 'revisions')));
//BEGIN SUGARCRM flav=pro ONLY
$layout->push(array('layout' => 'list-cluster', 'label' => 'Contracts', 'context' => array('link' => 'contracts')));
//END SUGARCRM flav=pro ONLY
$layout->push(array('layout' => 'list-cluster', 'label' => 'Accounts', 'context' => array('link' => 'accounts')));
$layout->push(array('layout' => 'list-cluster', 'label' => 'Contacts', 'context' => array('link' => 'contacts')));
$layout->push(array('layout' => 'list-cluster', 'label' => 'Opportunities', 'context' => array('link' => 'opportunities')));
$layout->push(array('layout' => 'list-cluster', 'label' => 'Cases', 'context' => array('link' => 'cases')));
$layout->push(array('layout' => 'list-cluster', 'label' => 'Bugs', 'context' => array('link' => 'bugs')));
//BEGIN SUGARCRM flav=pro ONLY
$layout->push(array('layout' => 'list-cluster', 'label' => 'Quotes', 'context' => array('link' => 'quotes')));
$layout->push(array('layout' => 'list-cluster', 'label' => 'Products', 'context' => array('link' => 'products')));
//END SUGARCRM flav=pro ONLY
$viewdefs['Documents']['base']['layout']['tabbed-layout'] = $layout->getLayout();