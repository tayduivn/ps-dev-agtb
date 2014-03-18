<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
$viewdefs['Styleguide']['base']['view']['field'] = array(
    'template_values' => array(

        // email widget mock data
        'email' => array(
            array(
                'email_address' => 'primary@example.info',
                'primary_address' => true,
                'opt_out' => false,
                'invalid_email' => false,
            ),
            array(
                'email_address' => 'optout@example.info',
                'primary_address' => false,
                'opt_out' => true,
                'invalid_email' => false,
            ),
            array(
                'email_address' => 'invalid@example.info',
                'primary_address' => false,
                'opt_out' => false,
                'invalid_email' => true,
            ),
            array(
                'email_address' => 'normal@example.info',
                'primary_address' => false,
                'opt_out' => false,
                'invalid_email' => false,
            ),
        ),

        // datetimecombo field mock data
        'datetimecombo' => '2013-05-06T22:47:00+00:00',

        // date field mock data
        'date' => '2013-05-06T22:47:00+00:00',

        // currency field mock data
        'currency' => array(
            'list_price' => 12345.7,
            'currency_id' => -99,
            'list_price_ERROR' => 'xyc',
        ),

        // date field mock data
        'bool' => array(
            'do_not_call' => 1,
            'do_not_call_ERROR' => 0,
        ),

        // date field mock data
        'text' => array(
            'description' => 'The styleguide module description.',
            'description_ERROR' => 'This description of the styleguide module is too long.',
        ),

        //phone field mock data
        'phone' => array(
            'phone_home' => '999-123-4567',
            'phone_home_ERROR' => '999-123-456',
        ),

        //url field
        'url' => array(
            'website' => 'http://www.sugarcrm.com',
            'website_ERROR' => 'http://www.sugar',
        ),

        //textarea field
        'textarea' => array(
            'description' => 'Dr. Max Wiznitzer, a pediatric neurologist and autism specialist at the Rainbow and Babies Childrens Hospital in Cleveland, Ohio, says this new study is a continuation of previous work in babies. He says this research makes sense to him. "There is a decrease in the amount of attention to eyes as an early marker of social behavior (think of it as a primitive level of socialization)." Wiznitzer suggests the failure to establish these early social skills has ramifications later as "social behavior shifts into more sophisticated patterns."',
            'description_ERROR' => 'This description of the styleguide module is too short.',
        ),

        //url field
        'password' => array(
            'secret_password' => 'asd@f23YAS#DFuu&',
            'secret_password_ERROR' => 'asdf',
        ),
    ),
);
