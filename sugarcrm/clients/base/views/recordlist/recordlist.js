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
    /**
     * @class View.RecordlistView
     * @alias SUGAR.App.view.views.RecordlistView
     * @extends View.FlexListView
     */
    extendsFrom: 'FlexListView',
    plugins: ['ellipsis_inline', 'list-column-ellipsis', 'error-decoration'],

    rowFields: {},

    contextEvents: {
        "list:editall:fire": "toggleEdit",
        "list:editrow:fire": "editClicked",
        "list:deleterow:fire": "deleteClicked"
    },

    initialize: function(options) {
        //Grab the record list of fields to display from the base metadata
        var recordListMeta = app.metadata.getView(null, 'recordlist') || {};
        options.meta = _.extend({}, recordListMeta, options.meta || {});

        app.view.views.FlexListView.prototype.initialize.call(this, options);
    },

    addActions:function () {
        if (this.actionsAdded) return;
        app.view.views.FlexListView.prototype.addActions.call(this);

        //Add Favorite to left
        this.addFavorite();

        //Add Save & Cancel
        var firstLeftColumn = this.leftColumns[0];
        if (firstLeftColumn && _.isArray(firstLeftColumn.fields)) {
            //Add Cancel button to left
            firstLeftColumn.fields.push({
                type:'editablelistbutton',
                label:'LBL_CANCEL_BUTTON_LABEL',
                name:'inline-cancel',
                css_class:'btn-link btn-invisible inline-cancel'
            });
            this.leftColumns[0] = firstLeftColumn;
        }
        var firstRightColumn = this.rightColumns[0];
        if (firstRightColumn && _.isArray(firstRightColumn.fields)) {
            //Add Save button to right
            firstRightColumn.css_class = 'overflow-visible';
            firstRightColumn.fields.push({
                type:'editablelistbutton',
                label:'LBL_SAVE_BUTTON_LABEL',
                name:'inline-save',
                css_class:'btn-primary'
            });
            this.rightColumns[0] = firstRightColumn;
        }
        this.actionsAdded = true;
    },

    addFavorite: function() {
        if (this.meta.favorite && this.leftColumns[0] && _.isArray(this.leftColumns[0].fields)) {
            this.leftColumns[0].fields.push({type:'favorite'});
        }
    },

    _render:function () {
        app.view.views.FlexListView.prototype._render.call(this);

        this.rowFields = {};
        _.each(this.fields, function(field) {
            //TODO: Modified date should not be an editable field
            //TODO: the code should be handled different way instead of checking its type later
            if(field.model.id && _.isUndefined(field.parent) && field.type !== 'datetimecombo') {
                this.rowFields[field.model.id] = this.rowFields[field.model.id] || [];
                this.rowFields[field.model.id].push(field);
            }
        }, this);
    },

    deleteClicked: function(model) {
        var self = this;
        app.alert.show('delete_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('NTC_DELETE_CONFIRMATION'),
            onConfirm: function() {
                model.destroy({
                    //Show alerts for this request
                    showAlerts: true,
                    success: function() {
                        self.collection.remove(model);
                        app.events.trigger("preview:close");
                        if(!self.disposed) self.render();
                    }
                });
            }
        });
    },

    editClicked: function(model) {
        this.toggleRow(model.id, true);
    },

    toggleRow: function(modelId, isEdit) {
        this.$("tr[name=" + this.module + "_" + modelId + "]").toggleClass("tr-inline-edit", isEdit);
        this.toggleFields(this.rowFields[modelId], isEdit);
    },

    toggleEdit: function(isEdit) {
        var self = this;
        this.viewName = isEdit ? 'edit' : 'list';
        _.each(this.rowFields, function(editableFields, modelId) {
            //running the toggling jon in each thread to prevent blocking browser performance
            _.defer(function(modelId) {
                self.toggleRow(modelId, isEdit);
            }, modelId);
        }, this);
    },

    /**
     *
     * @private
     */
    _dispose: function(){
        app.view.views.FlexListView.prototype._dispose.call(this);
        this.rowFields = null;
    },

    /**
     * Adds the favorite field to app.view.View.getFieldNames() if meta.favorites is true
     * so my_favorite is part of the field list and is fetched
     */
    getFieldNames: function(module) {
        var fields = app.view.View.prototype.getFieldNames.call(this, module);
        if (this.meta.favorite) {
            fields = _.union(fields, ['my_favorite']);
        }
        return fields;
    }
})
