<?php

function smarty_function_sugar_action_menu($params, &$smarty)
{
    $theme = $params['theme'] ? $params['theme'] : SugarThemeRegistry::current()->name;
    $addition_params = $params['params'];
    if($addition_params) {
        unset($params['params']);
        $params = array_merge_recursive($params, $addition_params);
    }

    if(is_array($params['buttons']) && $theme != 'Classic') {

        $menus = array(
            'html' => array_shift($params['buttons']),
            'items' => array()
        );

        foreach($params['buttons'] as $item) {
            if(strlen($item)) {
                array_push($menus['items'],array(
                   'html' => $item
               ));
            }
        }
        $action_menu = array(
            'id' => $params['id'] ? (is_array($params['id']) ? $params['id'][0] : $params['id']) : '',
            'htmlOptions' => array(
                'class' => $params['class'] && strpos($params['class'], 'clickMenu') !== false  ? $params['class'] : 'clickMenu '.$params['class'],
                'name' => $params['name'] ? $params['name'] : '',
                'title' => 'sugar_action_menu'
            ),
            'itemOptions' => array(
                'class' => (count($menus['items']) == 0) ? 'single' : ''
            ),
            'submenuHtmlOptions' => array(
                'class' => 'subnav'
            ),
            'items' => array(
                $menus
            )
        );
        require_once('function.sugar_menu.php');
        return smarty_function_sugar_menu($action_menu, $smarty);

    }

    if (is_array($params['buttons'])) {
        return '<div class="action_buttons">' . implode(' ', $params['buttons']).'</div>';
    } else if(is_array($params)) {
        return '<div class="action_buttons">' . implode(' ', $params).'</div>';
    }

    return $params['buttons'];
}

?>