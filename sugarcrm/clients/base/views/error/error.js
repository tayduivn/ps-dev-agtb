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
    className: 'error-page',

    initialize: function(options) {
        // Adds the metadata for the Error module
        app.metadata.set(this._metadata);
        app.data.declareModels();

        // Reprepare the context because it was initially prepared without metadata
        app.controller.context.prepare(true);

        // Attach the metadata to the view
        this.options.meta = this._metadata.modules[this.options.module].views[this.options.name].meta;
        app.view.View.prototype.initialize.call(this, options);
    },
    _render: function() {
        if(this.context.get('errorType')) {
            attributes = this.getErrorAttributes();
            this.model.set(attributes);
        }
        app.view.View.prototype._render.call(this);
    },
    getErrorAttributes: function() {
        var attributes = {};
        if(this.context.get('errorType') ==='404') {
            attributes = {
                title: 'ERR_HTTP_404_TITLE',
                type: 'ERR_HTTP_404_TYPE',
                message: 'ERR_HTTP_404_TEXT'
            };
        } else if(this.context.get('errorType') ==='500') {
           attributes = {
                title: 'ERR_HTTP_500_TITLE',
                type: 'ERR_HTTP_500_TYPE',
                message: 'ERR_HTTP_500_TEXT'
            };
        } else {
            attributes = {
                title: 'ERR_HTTP_DEFAULT_TITLE',
                type: 'ERR_HTTP_DEFAULT_TYPE',
                message: 'ERR_HTTP_DEFAULT_TEXT'
            };
        }
        return attributes;
    },

    _metadata : {
        "modules": {
            "Error": {
                "views": {
                    "error": {
                        "meta": {}
                    }
                },
                "layouts": {
                    "error": {
                        "meta": {
                            "type": "simple",
                            "components": [
                                {view: "error"}
                            ]
                        }
                    }
                }
            }
        }
    }
})
