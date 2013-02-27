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
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = 'edit';
        this.context.off("vcard:import:finish", null, this);
        this.context.on("vcard:import:finish", this.importVCard, this);
    },

    importVCard: function() {
        var self = this,
            vcardFile = $('[name=vcard_import]');

        if (_.isEmpty(vcardFile.val())) {
            app.alert.show('error_validation_vcard', {level:'error', title: app.lang.getAppString('LBL_EMPTY_VCARD'), messages: app.lang.getAppString('LBL_EMPTY_VCARD'), autoClose: true});
        }

        app.file.checkFileFieldsAndProcessUpload(self.model, {
            success: function (data) {
                var route = app.router.buildRoute(self.module, data.vcard_import, 'record');
                app.router.navigate(route, {trigger: true});
                app.alert.show('vcard-import-saved', {
                    level: 'success',
                    messages: app.lang.get('LBL_IMPORT_VCARD_SUCCESS', self.module),
                    autoClose: true
                });
            }
        },
        {deleteIfFails: true, htmlJsonFormat: false});
    }
})
