/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    /**
     * Sticky Rowaction does not disappear when user does not have access.  It becomes
     * disabled instead.  This allows us to keep things lined up nicely in Subpanel.
     *
     * @class View.Fields.StickyRowactionField
     * @alias SUGAR.App.view.fields.StickyRowactionField
     * @extends View.Fields.RowactionField
     */
    extendsFrom: 'RowactionField',
    /**
     * @param options
     * @override
     */
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'field', name: 'rowaction', method: 'initialize', args:[options]});
        this.type = 'rowaction';  //TODO Hack that loads rowaction templates.  I hope to remove this when SP-966 is fixed.
    },
    /**
     * We always render StickyRowactions and instead set disable class when the user has no access
     * @private
     */
    _render: function() {
        if(this.isDisabled()){
            if(_.isUndefined(this.def.css_class) || this.def.css_class.indexOf('disabled') === -1){
                this.def.css_class = (this.def.css_class) ? this.def.css_class + " disabled" : "disabled";
            }
            //Remove event listeners on this action since it is disabled
            this.undelegateEvents();
        }
        app.view.invokeParent(this, {type: 'field', name: 'rowaction', method: '_render'});
    },
    /**
     * Essentially the replacement of 'hasAccess' method for implementors of StickyRowactionField.
     * Used to determine if this rowaction should be rendered in a disabled state because the user lacks permission, etc.
     *
     * This is a default implementation disables when the user lacks access.
     * @return {boolean}
     */
    isDisabled: function(){
        return !app.view.invokeParent(this, {type: 'field', name: 'rowaction', method: 'hasAccess'});
    },
    /**
     * Forces StickyRowaction to be rendered and visible in Actiondropdowns.
     * @returns {boolean} TRUE always
     */
    hasAccess: function(){
        return true;
    }

})
