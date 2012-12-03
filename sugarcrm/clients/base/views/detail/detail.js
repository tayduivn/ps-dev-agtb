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
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ListView
 * @alias SUGAR.App.layout.ListView
 * @extends View.View
 */
({
    toggled: false,
    fieldsToDisplay: app.config.fieldsToDisplay || 5,
    events: {
        'click .more': 'toggleMoreLess',
        'click .less': 'toggleMoreLess'
    },
    _renderHtml: function() {
        app.view.View.prototype._renderHtml.call(this);
        var fieldsArray = this.$("span[sfuuid]") || [];

        if (fieldsArray.length > this.fieldsToDisplay) {
            _.each(fieldsArray, function(field, i) {
                if (i > this.fieldsToDisplay - 1) {
                    $(field).parent().parent().hide();
                }
            }, this);
            this.$(".more").removeClass("hide");
        }
        if (this.toggled) {
            this.toggleMoreLess();
        }
    },
    toggleMoreLess: function() {
        this.toggled = !this.toggled;
        var fieldsArray = this.$("span[sfuuid]") || [];
        var that = this;
        _.each(fieldsArray, function(field, i) {
            if (i > that.fieldsToDisplay - 1) {
                $(field).parent().parent().toggle();
            }
        });
        this.$(".less").toggleClass("hide");
        this.$(".more").toggleClass("hide");
    },
    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", function() {
                this.render();
            }, this);
        }
    }

})
