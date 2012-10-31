<?php

// Users
$seed = BeanFactory::getBean("Users");
$result = $seed->get_full_list();
foreach($result as $bean) {
    if(!empty($bean->first_name)) {
        $data[] = array(
            'module' => $bean->module_name,
            'name' => $bean->name,
            'id' => $bean->id,
        );
    }
}

// Contacts
$seed = BeanFactory::getBean("Contacts");
$result = $seed->get_full_list();
foreach($result as $bean) {
    if(!empty($bean->first_name)) {
        $data[] = array(
            'module' => $bean->module_name,
            'name' => $bean->name,
            'id' => $bean->id,
        );
    }
}

// Opportunities
$seed = BeanFactory::getBean("Opportunities");
$result = $seed->get_full_list();
foreach($result as $bean) {
    $data[] = array(
        'module' => $bean->module_name,
        'name' => $bean->name,
        'id' => $bean->id,
    );
}

// Accounts
$seed = BeanFactory::getBean("Accounts");
$result = $seed->get_full_list();
foreach($result as $bean) {
    $data[] = array(
        'module' => $bean->module_name,
        'name' => $bean->name,
        'id' => $bean->id,
    );
}
