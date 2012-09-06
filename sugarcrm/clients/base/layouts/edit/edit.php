<?php
<<<<<<< HEAD
/**
 * Created by JetBrains PhpStorm.
 * User: dwheeler
 * Date: 4/2/12
 * Time: 4:51 PM
 * To change this template use File | Settings | File Templates.
 */
=======
$viewdefs['base']['layout']['edit'] = array(
    'type' => 'simple',
    'components' =>
    array(
        0 => array(
            'view' => 'subnav',
        ),
        1 => array(
            'layout' =>
            array(
                'type' => 'fluid',
                'components' =>
                array(
                    0 => array(
                        'layout' =>
                        array(
                            'type' => 'simple',
                            'span' => 11,
                            'components' =>
                            array(
                                0 => array(
                                    'view' => 'edit',
                                ),
                           ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
>>>>>>> 3bf7542... seperates popup containers from modal layout widget
