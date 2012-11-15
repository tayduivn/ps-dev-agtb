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
    events: {
        'keyup .chzn-search input': 'throttleSearch'
    },
    /**
     * Initializes field and binds all function calls to this
     * @param {Object} options
     */
    initialize: function(options) {
        _.bindAll(this);
        this.app.view.Field.prototype.initialize.call(this, options);
        this.optionsTemplateC = this.app.template.getField(this.type, "options");
    },
    /**
     * Renders relate field
     */
    _render: function() {
        var self = this;
        var result = this.app.view.Field.prototype._render.call(this);
        this.$(".relateEdit").chosen({
            no_results_text: "Searching for " // TODO Add labels support
        }).change(function(event) {
            var selected = $(event.target).find(':selected');
            self.model.set(self.def.id_name, self.unformat(selected.attr('id')));
            self.model.set(self.def.name, self.unformat(selected.attr('value')));
        });
        return result;
    },
    /**
     * Throttles search ajax
     * @param {Object} e event object
     * @param {Integer} interval interval to throttle
     */
    throttleSearch: function(e, interval) {
        if (interval === 0 && e.target.value != "") {
            this.search(e);
            return;
        } else {
            interval = 500;
            clearTimeout(this.throttling);
            delete this.throttling;
        }

        this.throttling = setTimeout(this.throttleSearch, interval, e, 0);
    },
    /**
     * Searches for related field
     * @param event
     */
    search: function(event) {
        var self = this;
        var collection = app.data.createBeanCollection(this.def.module);
        collection.fetch({
            params: {basicSearch:event.target.value},  // TODO update this to filtering API
            success: function(data) {
                if (data.models.length > 0) {
                    self.options = data.models;
                    var options = self.optionsTemplateC(self);
                    self.$('select').html(options);
                    self.$('select').trigger("liszt:updated");
                } else {
                    //TODO trigger error we found nothing
                }
            }

        });
    }

})