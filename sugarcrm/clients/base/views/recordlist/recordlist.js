/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    /**
     * @class View.RecordlistView
     * @alias SUGAR.App.view.views.RecordlistView
     * @extends View.FlexListView
     */
    extendsFrom: 'FlexListView',
    plugins: ['ellipsis_inline', 'list-column-ellipsis', 'error-decoration', 'editable', 'tooltip'],

    /**
     * List of current inline edit models.
     *
     * @property
     */
    toggledModels: null,

    rowFields: {},

    contextEvents: {
        "list:editall:fire": "toggleEdit",
        "list:editrow:fire": "editClicked",
        "list:deleterow:fire": "deleteClicked"
    },

    initialize: function(options) {
        //Grab the record list of fields to display from the base metadata
        var recordListMeta = this._initializeMetadata();
        options.meta = _.extend({}, recordListMeta, options.meta || {});
        app.view.invokeParent(this, {type: 'view', name: 'flex-list', method: 'initialize', args:[options]});

        //Extend the prototype's events object to setup additional events for this controller
        this.events = _.extend({}, this.events, {
            'click [name=inline-cancel]' : 'resize'
        });

        //fire resize scroll-width on column add/remove
        this.on('list:toggle:column', this.resize, this);

        this.toggledModels = {};

        this.context._recordListFields = this.getFieldNames();
    },

    // Allows sub-views to override and use different view metadata if desired
    _initializeMetadata: function() {
        return app.metadata.getView(null, 'recordlist') || {};
    },

    addActions:function () {
        if (this.actionsAdded) return;
        app.view.invokeParent(this, {type: 'view', name: 'flex-list', method: 'addActions'});
        if(_.isUndefined(this.leftColumns[0])){
            //Add blank left column to contain favorite and inline-cancel buttons
            this.leftColumns.push({
                'type':'fieldset',
                'label': '',
                'sortable': false,
                'fields': []
            });
        }
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
        var favoritesEnabled = app.metadata.getModule(this.module, "favoritesEnabled");
        if (favoritesEnabled !== false
            && this.meta.favorite && this.leftColumns[0] && _.isArray(this.leftColumns[0].fields)) {
            this.leftColumns[0].fields.push({type:'favorite'});
        }
    },

    _render:function () {
        app.view.invokeParent(this, {type: 'view', name: 'flex-list', method: '_render'});
        this.rowFields = {};
        _.each(this.fields, function(field) {
             if(field.model.id && _.isUndefined(field.parent)) {
                this.rowFields[field.model.id] = this.rowFields[field.model.id] || [];
                this.rowFields[field.model.id].push(field);
            }
        }, this);
    },

    deleteClicked: function(model) {
        var self = this,
            deletedModel = _.clone(model);
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
                        if (!self.disposed) {
                            self.render();
                        }
                        
                        self.layout.trigger("list:record:deleted", deletedModel);
                    }
                });
            }
        });
    },

    /**
     * {@link app.plugins.view.editable}
     * Compare with last fetched data and return true if model contains changes.
     * if model contains changed attributes,
     * check whether those are among the editable fields or not.
     *
     * @return {Boolean} True if current inline edit model contains unsaved changes.
     */
    hasUnsavedChanges: function() {
        var firstKey = _.first(_.keys(this.rowFields)),
            formFields = [];

        _.each(this.rowFields[firstKey], function(field) {
            if (field.name) {
                formFields.push(field.name);
            }
            //Inspect fieldset children fields
            if (field.def.fields) {
                formFields = _.chain(field.def.fields)
                    .pluck('name')
                    .compact()
                    .union(formFields)
                    .value();
            }
        }, this);
        return _.some(_.values(this.toggledModels), function(model) {
            var changedAttributes = model.changedAttributes(model.getSyncedAttributes());

            if (_.isEmpty(changedAttributes)) {
                return false;
            }
            var unsavedFields = _.intersection(_.keys(changedAttributes), formFields);
            return !_.isEmpty(unsavedFields);
        }, this);
    },

    /**
     * Toggle the selected model's fields when edit is clicked.
     *
     * @param {Backbone.Model} model Selected row's model.
     */
    editClicked: function(model) {
        this.toggleRow(model.id, true);
        //check to see if horizontal scrolling needs to be enabled
        this.resize();
    },

    /**
     * Toggle editable selected row's model fields.
     *
     * @param {String} modelId Model Id.
     * @param {Boolean} isEdit True for edit mode, otherwise toggle back to list mode.
     */
    toggleRow: function(modelId, isEdit) {
        if (isEdit) {
            this.toggledModels[modelId] = this.collection.get(modelId);
        } else {
            delete this.toggledModels[modelId];
        }
        this.$('tr[name=' + this.module + '_' + modelId + ']').toggleClass('tr-inline-edit', isEdit);
        this.toggleFields(this.rowFields[modelId], isEdit);
    },

    /**
     * Toggle editable entire row fields.
     *
     * @param {Boolean} isEdit True for edit mode, otherwise toggle back to list mode.
     */
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
        app.view.invokeParent(this, {type: 'view', name: 'flex-list', method: '_dispose'});
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
