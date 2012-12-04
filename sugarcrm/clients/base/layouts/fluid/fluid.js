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
/**
 * Layout that places components using bootstrap fluid layout divs
 * @class View.Layouts.FluidLayout
 * @extends View.Layout
 */
({
    /**
     * Places a view's element on the page. This should be overriden by any custom layout types.
     * In layout defs, the child component should have a `span` definition corresponding to the bootstrap scaffold.
     * @param {View.View} comp
     * @protected
     * @method
     */
    _placeComponent: function(comp, def) {
        var compdef = def.layout || def.view,
            size = compdef.span || 4;

        if (!this.$el.children()[0]) {
            this.$el.addClass("container-fluid").append('<div class="row-fluid" style="clear:both"></div>');
        }

        //Create a new td and add the layout to it
        $().add("<div></div>").addClass("span" + size).append(comp.el).appendTo(this.$el.find("div.row-fluid")[0]);
    }
})