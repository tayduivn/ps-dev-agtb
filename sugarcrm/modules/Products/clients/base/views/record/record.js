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
    extendsFrom: 'RecordView',

    initialize: function(options) {
        this._setupCommitStageField(options.meta.panels);
        app.view.views.RecordView.prototype.initialize.call(this, options);
    },

    initButtons: function() {
        app.view.views.RecordView.prototype.initButtons.call(this);

        // if the model has a quote_id and it's not empty, disable the convert_to_quote_button
        if(this.model.has('quote_id') && !_.isEmpty(this.model.get('quote_id'))
            && !_.isUndefined(this.buttons['convert_to_quote_button'])) {
            this.buttons['convert_to_quote_button'].setDisabled(true);
        }
    },

    delegateButtonEvents: function() {
        this.context.on('button:convert_to_quote:click', this.convertToQuote, this);

        app.view.views.RecordView.prototype.delegateButtonEvents.call(this);
    },

    convertToQuote: function(e) {
        var alert = app.alert.show('info_quote', {
                        level: 'info',
                        autoClose: false,
                        closeable: false,
                        title: app.lang.get("LBL_CONVERT_TO_QUOTE_INFO", this.module) + ":",
                        messages: [app.lang.get("LBL_CONVERT_TO_QUOTE_INFO_MESSAGE", this.module)]
                    });
        // remove the close since we don't want this to be closable
        alert.$el.find('a.close').remove();

        var url = app.api.buildURL(this.model.module, 'quote', { id: this.model.id });
        var callbacks = {
            'success' : _.bind(function(resp, status, xhr) {
                app.alert.dismiss('info_quote');
                window.location.hash="#bwc/index.php?module=Quotes&action=EditView&record=" + resp.id;
            }, this),
            'error' : _.bind(function(resp, status, xhr) {
                app.alert.dismiss('info_quote');
                app.alert.show('error_xhr', {
                        level: 'error',
                        autoClose: true,
                        title: app.lang.get("LBL_CONVERT_TO_QUOTE_ERROR", this.module) + ":",
                        messages: [app.lang.get("LBL_CONVERT_TO_QUOTE_ERROR_MESSAGE", this.module)]
                    });
            }, this)
        };
        app.api.call("create", url, null, callbacks);
    },

    /**
     * Set up the commit_stage field based on forecast settings - if forecasts is set up, adds the correct dropdown
     * elements, if forecasts is not set up, it removes the field.
     * @param panels
     * @private
     */
    _setupCommitStageField: function(panels) {
        _.each(panels, function(panel) {
            if(!app.metadata.getModule("Forecasts", "config").is_setup) {
                panel.fields = _.filter(panel.fields, function (field) {
                    return field.name != "commit_stage";
                })
            } else {
                _.each(panel.fields, function(field) {
                    if (field.name == "commit_stage") {
                        field.options = app.metadata.getModule("Forecasts", "config").buckets_dom;
                    }
                })
            }
        });
    }
})
