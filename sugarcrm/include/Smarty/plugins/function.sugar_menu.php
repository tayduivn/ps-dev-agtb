<?php
/**
 * Touched: sugarcrm/include/Smarty/plugins/function.sugar_action_menu.php
 */
/**
 * @param $params
 * @param $smarty
 * @return string
 *
 * * @author Justin Park (jpark@sugarcrm.com)
 */
function smarty_function_sugar_menu($params, &$smarty)
{
    $root_options = $params['id'] ? 'id="'.$params['id'].'"' : "";
    if($params['htmlOptions']) {
        foreach($params['htmlOptions'] as $attr => $value) {
            $root_options .= $attr.'="'.$value.'" ';
        }
    }
    $output = '<ul '. $root_options .'>';
    foreach($params['items'] as $item) {
        $output .= "<li >{$item['html']}";
        if(isset($item['items']) && count($item['items'])) {
            $output .= smarty_function_sugar_menu(array(
                'items' => $item['items'],
                'htmlOptions' => $params['submenuHtmlOptions']
            ), $smarty);
        }
        $output .= "</li>";
    }
    $output .= '</ul>';
    return $output;
}
