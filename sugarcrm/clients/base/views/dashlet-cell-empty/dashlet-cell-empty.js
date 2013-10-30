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
        'click .widget.empty' : 'addClicked'
    },
    originalTemplate: null,
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        //use the dashboard model rather than the current page's
        this.model = this.layout.context.get("model");

        this.model.on("setMode", this.setMode, this);
        this.originalTemplate = this.template;
        this.setMode(this.model.mode);
    },
    addClicked: function(evt) {
        var self = this;
        app.drawer.open({
            layout: 'dashletselect',
            context: this.layout.context
        }, function(model) {
            if(!model) return;
            var conf = model.toJSON(),
                dash = {
                    context: {
                        module: model.get("module"),
                        link: model.get("link")
                    }
                },
                type = conf.componentType;
            delete conf.config;
            delete conf.componentType;
            if(_.isEmpty(dash.context.module) && _.isEmpty(dash.context.link)) {
                delete dash.context;
            }
            dash[type] = conf;
            self.layout.addDashlet(dash);
        });
    },
    setMode: function(type) {
        if(type === 'edit') {
            this.template = this.originalTemplate;
        } else if(type === 'drag') {
            this.template = app.template.getView(this.name + '.drop') || this.originalTemplate;
        } else {
            this.template = app.template.getView(this.name + '.empty') || app.template.empty;
        }
        this.render();
    },
    _dispose: function() {
        this.model.off("setMode", null, this);
        app.view.View.prototype._dispose.call(this);
    }
})
