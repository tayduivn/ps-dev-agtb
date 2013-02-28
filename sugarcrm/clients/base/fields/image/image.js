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
        "click .delete" : "delete"
    },
    fileUrl : "",
    _render: function() {
        this.model.fileField = this.name;
        app.view.Field.prototype._render.call(this);
        return this;
    },
    format: function(value){
        if (value) {
            value = this.buildUrl() + "&" + value;
        }
        return value;
    },
    buildUrl: function(options) {
        return app.api.buildFileURL({
                    module: this.module,
                    id: this.model.id,
                    field: this.name
                }, options);
    },
    delete: function() {
        var self = this;
        app.api.call('delete', self.buildUrl({htmlJsonFormat: false}), {}, {
                success: function(data) {
                    self.model.set(self.name, "");
                    self.render();
                },
                error: function(data) {
                    // refresh token if it has expired
                    app.error.handleHttpError(data, {});
                }}
        );
    },
    bindDomChange: function() {
        //Keep empty because you cannot set a value of a type `file` input
        //We don't trigger change event so we don't rerender
    },
    bindDataChange: function() {
        if (this.model) {
            this.model.on("change:" + this.name, function() {
                var isValue = this.$(this.fieldTag).val();
                if (!isValue) {
                    //Rerender only if the input type file is empty
                    this.render();
                }
            }, this);
        }
    }
})