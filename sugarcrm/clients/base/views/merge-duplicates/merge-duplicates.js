({
    plugins: ['editable', 'error-decoration'],
    extendsFrom: 'ListView',
    events: {
        'click a[data-action=more]' : 'showMore',
        'click a[data-mode=preview]' : 'togglePreview'
    },
    MAX_RECORDS: 5, // the number of records we can merge, by fiat
    mergeFields: [], // list of fields to generate the metadata on the fly
    rowFields: {},
    primaryRecord: {},
    filterDef: [],
    toggled: false,
    isPreviewOpen: false,

    /**
     *
     * {@inheritdoc}
     */
    initialize: function(options) {
        var meta = app.metadata.getView(options.module, 'record'),
            fieldDefs = app.metadata.getModule(options.module).fields,
            mergeCollection = options.context.get('collection'),
            records = this.checkAccessToModels(options.context.get("selectedDuplicates")),
            primary,
            ids;

        // bomb out if we don't have between 2 and MAX_RECORDS
        if (!records.length || records.length < 2 || records.length > this.MAX_RECORDS) {
            var msg = app.lang.get(records.length === options.context.get("selectedDuplicates") ?
                'ERR_MERGE_INVALID_NUMBER_RECORDS' : 'ERR_MERGE_NO_ACCESS', options.module);
            app.alert.show('invalid-record-count',{
                level: 'error',
                messages: msg,
                autoClose: true
            });
            app.drawer.close(false);
            return false;
        }

        // standardize primary record from list of records,
        // and put primary at the beginning of records.
        // this is useful primarily to know which record will be the primary
        // in the collection to be pulled later. We do not use the input models
        primary = (options.context.has("primaryRecord")) ?
            _.findWhere(records, {id: options.context.get("primaryRecord").id}) :
            _.first(records);
        records = [primary].concat(_.without(records, primary));

        // these are the fields we'll need to pull our records
        this.mergeFields = _.chain(meta.panels)
            .map(function(panel) {return this.flattenFieldsets(panel.fields);}, this)
            .flatten()
            .filter(function(field) {return field.name && this.validMergeField(fieldDefs[field.name]);}, this)
            .value();

        // enforce the order of the ids so that primaryRecord always appears first
        // and only retrieve the records specified
        ids = (_.pluck(records, 'id'));
        if (mergeCollection) {
            this.filterDef = mergeCollection.filterDef;
            mergeCollection.filterDef = mergeCollection.filterDef || [];
            mergeCollection.filterDef.push({ "id": { "$in" : ids}});
            mergeCollection.comparator = function (model) {
                return _.indexOf(ids, model.get('id'));
            }
        }
        app.view.View.prototype.initialize.call(this, options);
        this.setPrimaryRecord(primary);
        this.action = 'list';
        this.layout.on('mergeduplicates:save:fire', this.save, this);
    },

    /**
     *
     * @param {Array} models Models to check access for merge.
     * @return {Array} Model with access
     */
    checkAccessToModels: function(models) {
        var result = [];
        _.each(models, function(model) {
            if ( app.acl.hasAccessToModel('edit', model) &&
                app.acl.hasAccessToModel('list', model) &&
                app.acl.hasAccessToModel('delete', model)
            ) {
                result.push(model);
            }
        }, this);
        return result;
    },

    /**
     * Save primary and delete other records
     */
    save: function() {
        var self = this,
            alternativeModels = _.without(this.collection.models, this.primaryRecord),
            alternativeModelNames = [];
        _.each(alternativeModels, function(model) {
            alternativeModelNames.push(model.get('name'));
        });
        this.clearValidationErrors(this.getFieldNames());
        this.primaryRecord.doValidate(this.getFieldNames(), function(isValid) {
            if (isValid) {
                app.alert.show('merge_confirmation', {
                    level: 'confirmation',
                    messages: app.lang.get('LBL_MERGE_DUPLICATES_CONFIRM')
                        + " " + alternativeModelNames.join(", ") + ". "+ app.lang.get('LBL_MERGE_DUPLICATES_PROCEED'),
                    onConfirm: function () {
                        self.primaryRecord.save({}, {
                            success: function() {
                                _.each(alternativeModels, function (model) {
                                    self.collection.remove(model);
                                    model.destroy();
                                }, self);
                                // We need to wait untill all models removed from server
                                _.defer(function() {
                                    app.drawer.close(true);
                                }, self);
                            },
                            error: function () {
                                app.alert.show('server-error', {
                                    level: 'error',
                                    messages: app.lang.get('ERR_AJAX_LOAD_FAILURE'),
                                    autoClose: false
                                });
                            },
                            showAlerts: true,
                            viewed: true
                        });
                    }
                });
            }
        });
    },
    /**
     * Override the standard view's get field names.
     * @override
     * @returns {Array} array of field names.
     */
    getFieldNames: function() {
        var fields = [],
            fieldDefs = app.metadata.getModule(this.module).fields;
        _.each(this.mergeFields, function(field) {
            var def = fieldDefs[field.name];
            if (!_.isUndefined(def.id_name) && !_.isUndefined((fieldDefs[def.id_name].name))) {
                fields.push(fieldDefs[def.id_name].name);
            }
            fields.push(fieldDefs[def.name].name);
        }, this);
        return fields;
    },
    /**
     * Create a two panel viewdews metadata (visible, hidden) given list of fields
     * and the collection
     * @param {Array} fields the list of fields for the module
     * @param {BeanCollection} collection the collection of records to merge
     * @param {Model} primaryRecord the primary record
     * @return {Object} the metadata for the view template
     */
    generateMetadata: function(fields, collection, primaryRecord) {
        var hiddenFields = [],
            visibleFields = [];
        // the algorithm for determining field placement:
        // 1. all fields should be base fields. fieldsets should be broken. no non-editable fields.
        // 2. if a field is "similar" among all alternatives, it is placed in a hidden panel
        // 3. if a field is "different" among all alternatives (i.e. there exists two alternatives such
        //    that the field value is not equal), it is placed in a visible panel.
        _.each(fields, function(field) {
            // internal helper - see if the field is the same among all alternatives.
            function isSimilar(field, primary, alternatives) {
                return _.every(alternatives, function(alt) {
                    return (alt.get(field.name) === primary.get(field.name));
                });
            }

            var fieldMeta = {
                type: 'fieldset',
                label: field.label,
                fields: [
                    {
                        'name' : field.name,
                        'type' : 'duplicatecopy'
                    },
                    field
                ]
            };

            var alternatives = collection.without(primaryRecord);

            if(isSimilar(field, primaryRecord, alternatives)) {
                fieldMeta.oddEven = (hiddenFields.length + 1) % 2 ? 'odd' : 'even';
                hiddenFields.push(fieldMeta);
            }
            else {
                fieldMeta.oddEven = (visibleFields.length + 1) % 2 ? 'odd' : 'even';
                visibleFields.push(fieldMeta);
            }
        }, this);

        return {
            type: 'list',
            panels: [
                {
                    fields: visibleFields
                },
                {
                    hide: true,
                    fields: hiddenFields
                }
            ]
        };
    },

    /**
     * utility method for determining if a field is mergable from its fielddef.
     * @param fieldDef
     * @return {Boolean} is this field a valid field to merge?
     */
    validMergeField: function(fieldDef) {
        // these field names won't be mergeable.
        var fieldNameBlacklist = [
            'date_entered','date_modified','modified_user_id','created_by','deleted'
            ],
            // these attribute combos will be allowed to merge
            validArrayAttributes = [
                { type: 'datetimecombo', source: 'db' },
                { type: 'datetime', source: 'db' },
                { type: 'varchar', source:'db' },
                { type: 'enum', source: 'db' },
                { type: 'multienum', source: 'db' },
                { type: 'text', source: 'db' },
                { type: 'date', source: 'db' },
                { type: 'time', source: 'db' },
                { type: 'int', source: 'db' },
                { type: 'long', source: 'db' },
                { type: 'double', source: 'db' },
                { type: 'float', source: 'db' },
                { type: 'short', source: 'db' },
                { dbType: 'varchar', source: 'db' },
                { dbType: 'double', source: 'db' },
                { type: 'relate' }
            ];

        // need a field def to play.
        if (!fieldDef) {
            return false;
        }

        if (_.contains(fieldNameBlacklist, fieldDef.name)) {
            return false;
        }

        // the explicit merge flag
        if(_.has(fieldDef,'duplicate_merge')) {
            if (fieldDef.duplicate_merge === 'disabled' || fieldDef.duplicate_merge === false) {
                return false;
            }

            if(fieldDef.duplicate_merge === 'enabled' || fieldDef.duplicate_merge === true) {
                return true;
            }
        }

        // no autoincrement field please
        if(fieldDef.auto_increment === true) {
            return false;
        }

        // normalize fields that might not be there
        fieldDef.dbType = fieldDef.dbType || fieldDef.type;
        fieldDef.source = fieldDef.source || 'db';

        // compare to values in the list of acceptable attributes
        return _.some(validArrayAttributes, function(o) {
            return _.chain(o)
                    .keys()
                    .every(function(key) {
                        return o[key] === fieldDef[key];
                     })
                    .value();
        });
    },

    /**
     * utility method for taking a fieldlist with possible nested fields,
     * and returning a flat array of fields
     *
     * @param {Array} defs - unprocessed list of fields from metadata
     * @return {Array} fields - flat list of fields
     */
    flattenFieldsets: function(defs) {
        var fieldsetFilter = function(field) {
                return field.type && field.type === 'fieldset' && _.isArray(field.fields);
            },
            fields = _.reject(defs, fieldsetFilter),
            fieldsets = _.filter(defs, fieldsetFilter),
            sort = _.chain(defs).pluck('name').value() || [],
            sortTemp = [];

        while (fieldsets.length) {
            //collect fields' names from fieldset
            var fieldsNames = _.chain(fieldsets)
                .pluck('fields')
                .flatten()
                .pluck('name')
                .value();
            sortTemp = [];
            // create new sort sequence
            _.each(sort, function(value) {
                if (value === _.first(fieldsets).name) {
                    sortTemp = sortTemp.concat(fieldsNames);
                } else {
                    sortTemp = sortTemp.concat(value);
                }
            }, this);
            sort = sortTemp;
            // fieldsets need to be broken into component fields
            fieldsets = _.chain(fieldsets)
                .pluck('fields')
                .flatten()
                .value();

            // now collect the raw fields from the press
            fields = fields.concat(_.reject(fieldsets, fieldsetFilter));

            // do we have any more fieldsets to squash?
            fieldsets = _.filter(fieldsets, fieldsetFilter);
        }
        // sorting fields acording to sequence
        fields = _.sortBy(fields, function(value, index) {
            var result = index,
                name = value;
            if (!_.isUndefined(value.name)) {
                name = value.name;
                _.each(sort, function(valueSort, indexSort) {
                    if (valueSort == name) {
                        result = indexSort;
                    }
                });
            }
            return result;
        });
        return fields;
    },

    /**
     * Toggles a Preview for the primary record
     */
    togglePreview: function() {
        if(this.isPreviewOpen) {
            app.events.trigger("preview:close");
            this.isPreviewOpen = false;
        }
        else {
            this.updatePreviewRecord(this.primaryRecord);
            this.isPreviewOpen = true;
        }
    },
    /**
     * Create the preview panel for the model in question
     * @param model
     */
    updatePreviewRecord: function(model) {
        var module = model.module || model.get('_module');
        var previewCollection = app.data.createBeanCollection(module, [model]);
        app.events.trigger("preview:render", model, previewCollection, false);
    },

    showMore: function(evt) {
        this.toggled = !this.toggled;
        this.$(".less").toggleClass("hide");
        this.$(".more").toggleClass("hide");
        this.$(".col .extra").toggleClass('hide');
    },
    /**
     * Update the view's title
     * @param title
     */
    updatePrimaryTitle: function(title) {
        this.recordName = title;
        this.$('span.record-name').text(title);
    },
    /**
     * Determine the best title to use for this record
     * Either the 'name' field, or
     * @param model
     * @private
     * @return string record's title.
     */
    _getRecordTitle: function(model) {
        return (model.get('name') ||
            ((model.get('first_name') || '') + ' ' + (model.get('last_name') || '')) || '').trim();
    },
    _render:function () {
        this.meta = this.generateMetadata(this.mergeFields, this.collection, this.primaryRecord);

        app.view.invokeParent(this, {
            type: 'view',
            name: 'list',
            method: '_render'
        });

        delete this.rowFields;
        this.rowFields = {};
        _.each(this.fields, function(field) {
            //TODO: Modified date should not be an editable field
            //TODO: the code should be handled different way instead of checking its type later
            if(field.model.id && _.isUndefined(field.parent) && field.type !== 'datetimecombo') {
                this.rowFields[field.model.id] = this.rowFields[field.model.id] || [];
                this.rowFields[field.model.id].push(field);
            }
        }, this);
        this.setPrimaryEdit(this.primaryRecord.id);
        this.$('[rel="tooltip"]').tooltip();
        this.setDraggable();
        if (this.toggled) {
            this.toggleMoreLess();
        }
    },

    setDraggable: function() {
        var self = this,
        mergeContainer = this.$('[data-container=merge-container]');
        mergeContainer.find(".col .primary-lbl").sortable({
            connectWith: self.$(".col .primary-lbl"),
            appendTo: mergeContainer,
            axis: 'x',
            disableSelection: true,
            cursor: 'move',
            placeholder: 'primary-lbl-placeholder-span',
            start: function(event, ui) {
                self.$(".col .primary-lbl").addClass('primary-lbl-placeholder');
            },
            stop: function(event, ui) {
                var droppedTo = ui.item.parents('.col');
                self.$(".col .primary-lbl").removeClass('primary-lbl-placeholder');
                // short circuit if we didn't land on anything
                if (droppedTo.length === 0) {
                    self.$(".col .primary-lbl").sortable('cancel');
                    return;
                }
                self.setPrimaryEdit(droppedTo.data("recordid"));
            }
        });
    },

    /**
     * Do what we need to do when the primary record is set
     * @param {String} id the record representing the new primary model
     */
    setPrimaryEdit: function(id) {
        // make sure we get the model in the collection, with all fields in it.
        var primary_record = this.collection.get(id),
            old_primary_record = this.primaryRecord;

        if(primary_record) {
            this.setPrimaryRecord(primary_record);
            this.toggleFields(this.rowFields[primary_record.id], true);
        }

        // revert old primary record to standard record, unless we dropped on the same record.
        if (old_primary_record && old_primary_record !== primary_record) {
            this.toggleFields(this.rowFields[old_primary_record.id], false);
        }

        if (!_.isUndefined(id)) {
            this.$('.primary-edit-mode').removeClass('primary-edit-mode');
            this.$('[data-recordid=' + id + ']').addClass('primary-edit-mode');
        }
    },

    /**
     * Set primary record
     * @param {Model} model primary model
     */
    setPrimaryRecord: function(model) {
        if (this.primaryRecord === model) {
            return;
        }

        // turn off events on the old primary record if applicable
        if (this.primaryRecord instanceof Backbone.Model) {
            this.primaryRecord.off('change error:validation', null, this);
        }

        // get the new primary record wired up
        this.primaryRecord = model;
        this.updatePrimaryTitle(this._getRecordTitle(this.primaryRecord));
        if (this.isPreviewOpen) {
            this.updatePreviewRecord(this.primaryRecord);
        }

        this.primaryRecord.on('change', function(model){
            if (this.isPreviewOpen) {
                app.events.trigger('preview:close'); // either this or set a previewId on the model
                this.updatePrimaryTitle(this._getRecordTitle(model));
                this.updatePreviewRecord(model);
            }
        }, this);
        this.context.set("primaryRecord", this.primaryRecord);
    },


    /**
     * custom bindDataChange
     */
    bindDataChange: function() {
        if (!this.collection) {
            return;
        }
        this.collection.on('reset', function (coll) {
            if (coll.length) {
                this.setPrimaryRecord(coll.at(0));
            }
            this.render();
        }, this);
    },

    _dispose: function() {
        var mergeCollection = this.context.get('collection');
        if (this.primaryRecord instanceof Backbone.Model) {
            this.primaryRecord.off('change', null, this);
        }
        this.collection.off('reset', null, this);
        if (!_.isUndefined(mergeCollection)) {
            mergeCollection.filterDef = this.filterDef;
        }
        app.view.View.prototype._dispose.call(this);
    }
})
