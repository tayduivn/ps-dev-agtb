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
        'click [name="record-close"]': 'closeClicked',
        'click [name="record-close-new"]': 'closeNewClicked'
    },
    extendsFrom: 'RowactionField',
    initialize: function (options) {
        app.view.invokeParent(this, {type: 'field', name: 'rowaction', method:'initialize', args:[options]});
        this.type = 'rowaction';
    },
    closeClicked: function () {
        this._close(false);
    },
    closeNewClicked: function () {
        this._close(true);
    },
    /**
     * Override so we can have a custom hasAccess for closed status
     *
     * @returns {Boolean} true if it has aclAccess and status is not closed
     */
    hasAccess: function() {
        var acl = app.view.invokeParent(this, {type: 'field', name: 'button', method:'hasAccess'});
        return acl && this.model.get('status') !== 'Completed';
    },
    _close: function (createNew) {
        var self = this;

        this.model.set('status', 'Completed');
        this.model.save({}, {
            success: function () {
                app.alert.show('close_task_success', {level: 'success', autoClose: true, title: app.lang.get('LBL_TASK_CLOSE_SUCCESS', self.module)});
                if (createNew) {
                    var module = app.metadata.getModule(self.model.module);
                    var prefill = app.data.createBean(self.model.module);
                    prefill.copy(self.model);

                    if (module.fields.status && module.fields.status['default']) {
                        prefill.set('status', module.fields.status['default']);
                    } else {
                        prefill.unset('status');
                    }

                    app.drawer.open({
                        layout: 'create-actions',
                        context: {
                            create: true,
                            model: prefill
                        }
                    }, function () {
                        if (self.parent) {
                            self.parent.render();
                        } else {
                            self.render();
                        }
                    });
                }
            },
            error: function (error) {
                app.alert.show('close_task_error', {level: 'error', autoClose: true, title: app.lang.getAppString('ERR_AJAX_LOAD')});
                app.logger.error('Failed to close a task. ' + error);

                // we didn't save, revert!
                self.model.revertAttributes();
            }
        });
    },
    bindDataChange: function () {
        if (this.model) {
            this.model.on("change:status", this.render, this);
        }
    }
})
