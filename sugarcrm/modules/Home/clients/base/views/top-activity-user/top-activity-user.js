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
    plugins: ['Dashlet', 'GridBuilder'],
    events: {
        'change select[name=filter_duration]': 'filterChanged'
    },
    initDashlet: function(viewName) {
        this.collection = new app.BeanCollection();
        if(!this.meta.config) {
            this.collection.on("reset", this.render, this);
        } else {
            // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
            app.view.invokeParent(this, {type: 'view', name: 'record', method: '_buildGridsFromPanelsMetadata', args: [this.meta.panels]});
        }
    },
    _mapping: {
        meetings: {
            icon: 'icon-comments',
            label: 'LBL_MOST_MEETING_HELD'
        },
        inbound_emails: {
            icon: 'icon-envelope',
            label: 'LBL_MOST_EMAILS_RECEIVED'
        },
        outbound_emails: {
            icon: 'icon-envelope-alt',
            label: 'LBL_MOST_EMAILS_SENT'
        },
        calls: {
            icon: 'icon-phone',
            label: 'LBL_MOST_CALLS_MADE'
        }
    },
    loadData: function(params) {
        if(this.meta.config) {
            return;
        }
        var url = app.api.buildURL('mostactiveusers', null, null, {days: this.settings.get("filter_duration")}),
            self = this;
        app.api.call("read", url, null, {
            success: function(data) {
                if(self.disposed) {
                    return;
                }
                var models = [];
                _.each(data, function(attributes, module){
                    if(_.isEmpty(attributes)) {
                        return;
                    }
                    var model = new app.Bean(_.extend({
                        id: _.uniqueId('aui')
                    }, attributes));
                    model.module = module;
                    model.set("name", model.get("first_name") + ' ' + model.get("last_name"));
                    model.set("icon", self._mapping[module]['icon']);
                    var template = Handlebars.compile(app.lang.get(self._mapping[module]['label'], self.module));
                    model.set("label", template({
                        count: model.get("count")
                    }));
                    model.set("pictureUrl", app.api.buildFileURL({
                        module: "Users",
                        id: model.get("user_id"),
                        field: "picture"
                    }));
                    models.push(model);
                }, this);
                self.collection.reset(models);
            },
            complete: params ? params.complete : null
        });
    },
    filterChanged: function(evt) {
        this.loadData();
    },

    _dispose: function() {
        if(this.collection) {
            this.collection.off("reset", null, this);
        }
        app.view.View.prototype._dispose.call(this);
    }
})
