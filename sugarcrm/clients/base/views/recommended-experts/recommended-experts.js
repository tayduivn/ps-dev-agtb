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
        "click .find-experts": "getRecommendations",
        "keyup .job-title": "submit"
    },

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
        this.getJobTitles();
        this.collection = app.data.createBeanCollection("Users");
    },

    _render: function() {
        app.view.View.prototype._render.call(this);

        if (this.$(".job-title") && this.typeahead_collection) {
            this.$(".job-title").typeahead({source: this.typeahead_collection});
        }
    },

    getJobTitles: function() {
        var self = this,
            url = app.api.buildURL(this.module, "expertsTypeahead",
                {"id": app.controller.context.get("model").id});

        app.api.call("read", url, null, { success: function(data) {
            self.typeahead_collection = data;
            if( self.$(".job-title") ) {
                self.$(".job-title").typeahead({source: self.typeahead_collection});
            }
        }});
    },

    submit: function(e) {
        if( this.$(".job-title").val().length && e.keyCode === 13 ) {
            this.getRecommendations();
        }
    },

    getRecommendations: function() {
        var self = this;
            this.jobTitle = this.$(".job-title").val();

        if( this.jobTitle.length ) {
            // build the URL for the custom "experts" REST endpoint
            var url = app.api.buildURL(this.module, "experts",
                {"id": app.controller.context.get("model").id},
                {"title": this.jobTitle});

            app.api.call("read", url, null, { success: function(data) {
                if (self.disposed) {
                    return;
                }
                self.collection.reset();
                if( data.length ) {
                    _.each(data, function(key, value) {
                        data[value]["guid"] = _.uniqueId("recommended-experts-item");
                        data[value]["picture_url"] = data[value]["picture"] ? app.api.buildFileURL({
                            module: "Users",
                            id: data[value]["id"],
                            field: "picture"
                        }) : "../styleguide/assets/img/profile.png";

                        var model = app.data.createBean("User");
                        model.attributes = data[value];
                        self.collection.add(model);
                    });
                }
                self.render();
            }});
        }
    }
})
