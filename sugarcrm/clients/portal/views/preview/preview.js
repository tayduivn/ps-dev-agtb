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
 * View that displays a model pulled from the activities stream.
 * @class View.Views.PreviewView
 * @alias SUGAR.App.view.views.PreviewView
 * @extends View.View
 */
    events: {
        'click .closeSubdetail': 'closePreview'
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "detail";
        app.events.off("preview:render", null, this).on("preview:render", this._renderPreview, this);
        app.events.off("preview:close", null, this).on("preview:close", this.closePreview,  this);
    },

    _render: function() {
        this.$el.parent().parent().addClass("container-fluid tab-content").attr("id", "folded");
    },
    _renderHtml: function() {
        var fieldsArray, that;
        app.view.View.prototype._renderHtml.call(this);
    },

    _renderPreview: function(model) {
        var fieldsToDisplay = app.config.fieldsToDisplay || 5;
        if(model) {
            // Create a corresponding Bean and Context for clicked search result. It
            // might be a Case, a Bug, etc...we don't know, so we build dynamically.
            this.model = app.data.createBean(model.get('_module'), model.toJSON());
            this.context.set({
                'model': this.model,
                'module': this.model.module
            });

            // Get the corresponding detail view meta for said module
            this.meta = app.metadata.getView(this.model.module, 'detail') || {};

            //Store copy of fields so we don't update it by ref
            var oFields = this.meta.panels[0].fields;

            // Clip meta panel fields to first N number of fields per the spec
            this.meta.panels[0].fields = _.first(this.meta.panels[0].fields, fieldsToDisplay);

            app.view.View.prototype._render.call(this);

            // restore copy of fields
            this.meta.panels[0].fields = oFields;
        }
    },
    closePreview: function() {
        this.model.clear();
        this.$el.empty();
        $("li.search").removeClass("on");
    }

})

