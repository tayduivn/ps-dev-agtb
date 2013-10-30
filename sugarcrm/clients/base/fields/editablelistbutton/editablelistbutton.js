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
        'click [name=inline-save]' : 'saveClicked',
        'click [name=inline-cancel]' : 'cancelClicked'
    },
    extendsFrom: 'ButtonField',
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'field', name: 'button', method: 'initialize', args:[options]});
        if(this.name === 'inline-save') {
            this.model.off("change", null, this);
            this.model.on("change", function() {
                this.changed = true;
            }, this);
        }
    },
    _loadTemplate: function() {
        app.view.Field.prototype._loadTemplate.call(this);
        if(this.view.action === 'list' && _.indexOf(['edit', 'disabled'], this.action) >= 0 ) {
            this.template = app.template.getField('button', 'edit', this.module, 'edit');
        } else {
            this.template = app.template.empty;
        }
    },
    /**
     * Called whenever validation completes on the model being edited
     * @param {boolean} isValid TRUE if model is valid
     * @private
     */
    _validationComplete : function(isValid){
        if (!isValid) {
            this.setDisabled(false);
            return;
        }
        if (!this.changed) {
            this.cancelEdit();
            return;
        }
        var self = this,
            options = {
                success: function(model) {
                    self.changed = false;
                    self.view.toggleRow(model.id, false);
                    self._refreshListView();
                },
                complete: function() {
                    self.setDisabled(false);
                },
                //Show alerts for this request
                showAlerts: {
                    'process': true,
                    'success': {
                        messages: app.lang.get('LBL_RECORD_SAVED', self.module)
                    }
                },
                relate: self.model.link ? true : false
            };

        options = _.extend({}, options, self.getCustomSaveOptions(options));

        var callbacks = {
            success: function() {
                self.model.save({}, options);
            }
        };

        async.forEachSeries(this.view.rowFields[this.model.id], function(view, callback) {
            app.file.checkFileFieldsAndProcessUpload(view, {
                success: function() {
                    callback.call();
                }
            }, {deleteIfFails: false }, true);
        }, callbacks.success);
    },

    getCustomSaveOptions: function(options) {
        return {};
    },

    saveModel: function() {
        this.setDisabled(true);
        var fieldsToValidate = this.view.getFields(this.module);
        this.model.doValidate(fieldsToValidate, _.bind(this._validationComplete, this));
    },
    cancelEdit: function() {
        if (this.isDisabled()) {
            this.setDisabled(false);
        }
        this.changed = false;
        this.model.revertAttributes();
        this.view.clearValidationErrors();
        this.view.toggleRow(this.model.id, false);
    },
    saveClicked: function(evt) {
        this.saveModel();
    },
    cancelClicked: function(evt) {
        this.cancelEdit();
    },
    /**
     * On model save success, this function gets called to refresh the list view
     * @see BaseFavoriteField is using about the same method
     * @private
     */
    _refreshListView: function() {
        var filterPanelLayout = this.view;
        //Try to find the filterpanel layout
        while (filterPanelLayout && filterPanelLayout.name!=='filterpanel') {
            filterPanelLayout = filterPanelLayout.layout;
        }
        //If filterpanel layout found and not disposed, then pick the value from the quicksearch input and
        //trigger the filtering
        if (filterPanelLayout && !filterPanelLayout.disposed && this.collection) {
            filterPanelLayout.applyLastFilter(this.collection);
        }
    }
})
