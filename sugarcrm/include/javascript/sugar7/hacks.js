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
(function(app) {
    var _oldMetadataSet = app.metadata.set;
    /**
     * Hack the metadata to fix teamset type
     * @param data
     */
    app.metadata.set = function(data){
        _.each(data.modules, function(module){
            if (!_.isUndefined(module.fields)) {
                var field = module.fields.team_name;
                if (field) {
                    delete field.len;
                    field.type = "teamset";
                }

                _.each(module.fields,function(field) {
                    // Metadata is invalid for relate fields like "account_id"
                    // In certain cases, their type is "relate" and source is "non-db"
                    // See bug 60632
                    if  (field.name &&
                        (field.type === "relate") &&
                        // ends with "_id"
                        (field.name.length > 2 &&
                            (field.name.length -
                             field.name.lastIndexOf("_id")) === 3))
                    {
                        field.type = "id";
                        delete field.source;
                    }
                });
            }
        }, this);
        _oldMetadataSet.apply(this, arguments);
    };
})(SUGAR.App);

// Hack to workaround latest select2 chrome "Oh Snap" crashes
// Tracked here: SP-805
// https://codereview.chromium.org/14060024
// https://github.com/ivaynberg/select2/issues/1099
// TODO: We need to remove this when fix is pushed to stable chrome released
WebKitMutationObserver = function() {};
WebKitMutationObserver.prototype.observe = function() {};