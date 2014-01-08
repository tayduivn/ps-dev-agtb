/*********************************************************************************
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
 ********************************************************************************/
/* Needed so that on iPad, when dismissing the keyboard
 * by clicking out of the input, the fixed elements
 * will not be in the center of the screen
 */
var _inputFocused = null;
if (Modernizr.touch) {
    $(document).on('blur', 'input, textarea', function() {
        _inputFocused = setTimeout(function() {
            window.scrollTo(document.body.scrollLeft, document.body.scrollTop);
        }, 0);
    });
    $(document).on('focus', 'input, textarea', function() {
        clearTimeout(_inputFocused);
    });
}
