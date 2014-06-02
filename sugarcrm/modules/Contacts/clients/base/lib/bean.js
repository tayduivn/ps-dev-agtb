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
//BEGIN SUGARCRM flav=ent ONLY
/**
 * Extensions to the Contacts sidecar bean.
 * Included by JSGroupings.php
 */
(function(app) {
    app.events.on("app:sync:complete", function(){

        //Very important to prevent infinite loop. Otherwise it is Bean initialize method who is extended
        if (!app.data.getModelClasses()['Contacts']) {
            return;
        }

        var contactsClass = app.data.getBeanClass("Contacts");

        /**
         * Custom validation needed for Contacts beans when changing portal_name field.  We need to do a uniqueness
         * check to make sure no two contacts have the same portal_name since it is the user id for Portal.
         *
         * @param {Object} fields Hash of field definitions to validate.
         * @param {Object} errors Error validation errors
         * @param {Function} callback Async.js waterfall callback
         */
        contactsClass.prototype._doValidatePortalName = function(fields, errors, callback) {
            var self = this,
                skip = false;
            if(_.isUndefined(this.get("id"))){
                // If new and portal_name is not set, skip checking portal_name
                if(!this.has("portal_name") || this.get("portal_name") === ""){
                    skip = true;
                }
            } else {
                // If not new and portal name has not changed since last sync, skip checking portal_name
                if(_.isUndefined(this.changedAttributes(this.getSyncedAttributes())["portal_name"])){
                    skip = true;
                }
            }

            if(skip){
                callback(null, fields, errors);
                return;
            }

            // portal_name was changed
            var currentName = self.get("portal_name");
            var alertOptions = {
                title: app.lang.get("LBL_VALIDATING"),
                level: "process"
            };
            app.alert.show('validation', alertOptions);

            app.api.records('read', 'Contacts', null, {
                filter: [
                    {
                        portal_name: currentName
                    }
                ]
            }, {
                success: function(data){
                    if(data.records && data.records.length > 0){
                        /**
                         * If there is more than one Contact with this portal_name
                         * or the found record is not the same as the current one,
                         *   then we have a duplicate.
                         */
                        if(data.records.length > 1 || data.records[0].id != self.get("id")){
                            errors['portal_name'] = {
                                ERR_EXISTING_PORTAL_USERNAME: ''
                            };
                        }
                    }
                },
                error: function(){
                    errors['portal_name'] = {
                        ERR_PORTAL_NAME_CHECK: ''
                    };
                },
                /**
                 * After check is done, close alert and trigger the completion of the validation to the editor
                 */
                complete: function(){
                    app.alert.dismiss('validation');
                    callback(null, fields, errors);
                }
            });
            return;
        };

        /**
         * Adds the validation task to the model
         * @override
         * @param options
         */
        contactsClass.prototype.initialize = function(options) {
            app.data.beanModel.prototype.initialize.call(this, options);

            this.addValidationTask('portal_name_unique', _.bind(this._doValidatePortalName, this));
        };
    });



})(SUGAR.App);
//END SUGARCRM flav=ent ONLY
