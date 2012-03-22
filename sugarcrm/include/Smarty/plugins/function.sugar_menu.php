<?php
/**
 * Touched: sugarcrm/include/Smarty/plugins/function.sugar_action_menu.php
 */
/**
<<<<<<< HEAD
 * @param $params
 * @param $smarty
 * @return string
 *
=======
 * Smarty plugin:
 * This is a Smarty plugin to create a multi-level menu using nasted ul lists.
 * The generated structure looks like this.
 * <ul $htmlOptions>
 *      <li $itemOptions>
 *          <elem></elem>
 *          <ul $submenuHtmlOptions>
 *              <li $itemOptions></li>
 *              <li $itemOptions>
 *                  <elem></elem>
 *                  <ul $submenuHtmlOptions>
 *                      <li $itemOptions></li>
 *                      ...
 *                  </ul>
 *              </li>
 *              ...
 *          </ul>
 *      </li>
 *      ...
 *  </ul>
 *
 *
 * @param $params array - look up the bellow example
 * @param $smarty
 * @return string - generated HTML code
 *
 * <pre>
 * smarty_function_sugar_menu(array(
 *      'id' => $string, //id property that is applied in root UL
 *      'items' => array(
 *          array(
 *              'html' => $html_string, //html container that renders in the LI tag
 *              'items' => array(), //nasted ul lists
 *          )
 *      ),
 *      'htmlOptions' => attributes that is applied in root UL, such as class, or align.
 *      'itemOptions' => attributes that is applied in LI items, such as class, or align.
 *      'submenuHtmlOptions' => attributes that is applied in child UL, such as class, or align.
 * ), $smarty);
 *
 * </pre>
>>>>>>> 93033b3367e6e6c2ff0e13175251922d9527d9b6
 * * @author Justin Park (jpark@sugarcrm.com)
 */
function smarty_function_sugar_menu($params, &$smarty)
{
<<<<<<< HEAD
    $root_options = $params['id'] ? 'id="'.$params['id'].'"' : "";
    if($params['htmlOptions']) {
        foreach($params['htmlOptions'] as $attr => $value) {
            $root_options .= $attr.'="'.$value.'" ';
        }
    }
    $output = '<ul '. $root_options .'>';
    foreach($params['items'] as $item) {
        $output .= "<li >{$item['html']}";
=======
    $root_options = array(
        "id" => $params['id'] ? $params['id'] : ""
    );
    if($params['htmlOptions']) {
        foreach($params['htmlOptions'] as $attr => $value) {
            $root_options[$attr] = $value;
        }
    }
    $output = open_tag("ul", $root_options);
    foreach($params['items'] as $item) {
        if(strpos($item['html'], "</") === 0) {
            $output .= $item['html'];
            continue;
        }
        $output .= open_tag('li', $params['itemOptions']).$item['html'];
>>>>>>> 93033b3367e6e6c2ff0e13175251922d9527d9b6
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
<<<<<<< HEAD
=======

function open_tag($tagName, $params = array()) {
    $options = "";
    foreach($params as $attr => $value) {
        if($value)
            $options .= $attr.'="'.$value.'" ';
    }
    return "<{$tagName} {$options}>";
}
>>>>>>> 93033b3367e6e6c2ff0e13175251922d9527d9b6
