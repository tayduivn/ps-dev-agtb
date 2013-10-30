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
/**
 * View that displays edit view on a model
 * @class View.Views.BaseEditView
 * @alias SUGAR.App.layout.BaseEditView
 * @extends View.View
 */
({
    /**
     * Resets the error messages for all fields that have been changed and sent on the models change event.
     * @param {object} model that was changed.
     * @param {object} object that holds the changed fields.
     */
    clearValidationError: function(model, fields) {
        var self = this;
        if(!_.isEmpty(fields.changes)){
            _.each(fields.changes, function (num, key) {
                var field = self.getField(key);

                if (field) {
                    var controlGroup = field.$el.parents('.control-group:first');

                    if (controlGroup) {
                        controlGroup.removeClass("error");
                        controlGroup.find('.add-on').remove();
                        controlGroup.find('.help-block').html("");
                    }
                }
            });
        }
    },

    /**
     * Highlights all fields that fails field validation during save.
     * @param {object} Object containing the fields that failed validation.
     */
    handleValidationError:function (errors) {
        var self = this;

        _.each(errors, function (fieldErrors, fieldName) {
            //retrieve the field by name
            var field = self.getField(fieldName);
            var ftag = this.fieldTag || '';

            if (field) {
                var controlGroup = field.$el.parents('.control-group:first');

                if (controlGroup) {
                    controlGroup.addClass("error");
                    controlGroup.find('.add-on').remove();
                    controlGroup.find('.help-block').html("");

                    if (field.$el.parent().parent().find('.input-append').length > 0) {
                        field.$el.unwrap()
                    }
                    // Add error styling
                    field.$el.wrap('<div class="input-append  '+ftag+'">');

                    _.each(fieldErrors, function (errorContext, errorName) {
                        controlGroup.find('.help-block').append(app.error.getErrorString(errorName, errorContext));
                    });

                    $('<span class="add-on"><i class="icon-exclamation-sign"></i></span>').insertBefore(controlGroup.find('.help-block'));
                }
            }
        });
    }
})


