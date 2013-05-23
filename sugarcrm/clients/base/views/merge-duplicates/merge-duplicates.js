({
    extendsFrom: 'ListView',
    events: {
        'click .show_extra' : 'showMore',
        'click .preview' : 'togglePreview'
    },
    MAX_RECORDS: 5, // the number of records we can merge, by fiat
    mergeFields: [], // list of fields to generate the metadata on the fly
    rowFields: {},
    primaryRecord: {},
    recordName: '',
    isPreviewOpen: false,
    initialize: function(options) {
        var meta = app.metadata.getView(options.module, 'record'),
            fieldDefs = app.metadata.getModule(options.module).fields,
            mergeCollection = options.context.get('collection'),
            records = options.context.get("selectedDuplicates"),
            primary,
            ids;

        // bomb out if we don't have between 2 and MAX_RECORDS
        if (!records.length || records.length < 2 || records.length > this.MAX_RECORDS) {
            app.alert.show('invalid-record-count',{
                level: 'error',
                messages: app.lang.get('ERR_MERGE_INVALID_NUMBER_RECORDS',options.module),
                autoClose: true
            });
            app.drawer.close(false);
        }

        // standardize primary record from list of records,
        // and put primary at the beginning of records.
        // this is useful primarily to know which record will be the primary
        // in the collection to be pulled later. We do not use the input models
        primary = (options.context.has("primaryRecord")) ?
            _.findWhere(records,{id: options.context.get("primaryRecord").id}) :
            records[0];
        records = [primary].concat(_.without(records, primary));
        
        // these are the fields we'll need to pull our records
        this.mergeFields = _.chain(meta.panels)
            .map(function(panel) {return this.flattenFieldsets(panel.fields); }, this)
            .flatten()
            .filter(function(field) { return field.name && this.validMergeField(fieldDefs[field.name]); }, this)
            .value();

        // enforce the order of the ids so that primaryRecord always appears first
        // and only retrieve the records specified
        ids = (_.pluck(records,'id'));
        if (mergeCollection) {
            mergeCollection.filterDef = [{ "id": { "$in" : ids}}];
            mergeCollection.comparator = function (model) {
                return _.indexOf(ids,model.get('id'));
            }
        }

        app.view.View.prototype.initialize.call(this, options);

        this.action = 'list';
        this.layout.on('mergeduplicates:save:fire', this.save, this);
    },
    /**
     * Save primary and delete other records
     */    
    save: function() {
        var self = this,
            alternativeModels = this.collection.clone().remove(this.primaryRecord),
            alternativeModelNames = alternativeModels.pluck('name');
        app.alert.show('merge_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('LBL_MERGE_DUPLICATES_CONFIRM') + " "+ alternativeModelNames.join(", ") + ". "+ app.lang.get('LBL_MERGE_DUPLICATES_PROCEED'),
            onConfirm: function () {
                self.primaryRecord.save({}, {
                    success: function() {
                        alternativeModels.each(function (model) {
                            model.destroy();
                        }); 
                        app.drawer.close(true);
                    },
                    error: function () {
                        app.alert.show('server-error', {
                            level: 'error',
                            messages: app.lang.get('ERR_AJAX_LOAD_FAILURE'),
                            autoClose: false
                        });
                    }
                });
            }
        });
    },
    /**
     * Override the standard view's get field names.
     * @override
     * @param module
     * @returns {Array} array of field names.
     */
    getFieldNames: function(module) {
        return _.pluck(this.mergeFields,'name');
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
        _.each(fields, function(field, index){
            // internal helper - see if the field is the same among all alternatives.
            function isSimilar(field, primary, alternatives) {
                return _.every(alternatives, function(alt) {
                    return (alt.get(field.name) == primary.get(field.name));
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
                fieldMeta.oddEven = (hiddenFields.length + 1)%2 ? 'odd' : 'even';
                hiddenFields.push(fieldMeta);
            }
            else {
                fieldMeta.oddEven = (visibleFields.length + 1)%2 ? 'odd' : 'even';
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
            validArrayAttributes = [{
                type: 'datetimecombo',
                source: 'db'
            }, {
                type: 'datetime',
                source: 'db'
            }, {
                type: 'varchar',
                source:'db'
            }, {
                type: 'enum',
                source: 'db'
            }, {
                type: 'multienum',
                source: 'db'
            }, {
                type: 'text',
                source: 'db'
            }, {
                type: 'date',
                source: 'db'
            }, {
                type: 'time',
                source: 'db'
            }, {
                type: 'int',
                source: 'db'
            }, {
                type: 'long',
                source: 'db'
            }, {
                type: 'double',
                source: 'db'
            }, {
                type: 'float',
                source: 'db'
            }, {
                type: 'short',
                source: 'db'
            }, {
                dbType: 'varchar',
                source: 'db'
            }, {
                dbType: 'double',
                source: 'db'
            }, {
                type: 'relate'
            }];

        // need a field def to play.
        if (!fieldDef) {
            return false;
        }

        if(_.contains(fieldNameBlacklist, fieldDef.name)) {
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
     * coming soon - type filtering
     * @param {Array} defs - unprocessed list of fields from metadata
     * @return {Array} fields - flat list of fields
     */
    flattenFieldsets: function(defs) {
        var fields,
           fieldsets,
           fieldsetFilter = function(field) {
                return field.type && field.type === 'fieldset' && _.isArray(field.fields);
           };

        fields = _.reject(defs, fieldsetFilter);
        fieldsets = _.filter(defs, fieldsetFilter);

        while (fieldsets.length) {
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
        var btn = this.$("a.show_extra"),
            newHtml = (btn.text().trim() == "More") ?
                'Less <i class="icon-caret-up"></i>' :
                'More <i class="icon-caret-down"></i>';

        btn.html(newHtml);
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
        this.setSortable();
        this.setDraggable();
    },

    setSortable: function() {
        this.$(".fluid-div").sortable({
            items: ".col",
            axis: "x"
        });
        this.$(".fluid-div").disableSelection();
    },

    setDraggable: function() {
        var self = this,
            dragMe = _.bind(this.setDraggable,this); // avoid losing our context in the recursion

        this.$( ".primary-edit-mode .primary-lbl" ).draggable({
            scroll: true,
            helper: function( event ) {
                return $('<div class="primary-lbl static-ui-draggable"> Primary</div>');
            },
            stop: function(e) {
                var dropped_to = self.$(document.elementFromPoint(e.clientX, e.clientY+24)).closest('.col');

                // short circuit if we didn't land on anything
                if (!dropped_to.length) {
                    return;
                }

                // style cleanup
                self.$('.col').removeClass('primary-edit-mode');
                self.$('.col .primary-lbl').removeAttr('style');
                dropped_to.addClass('primary-edit-mode');

                self.setPrimaryEdit(dropped_to.data("recordid"));
                _.delay(dragMe, 500);
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
            //app.view.views.ListView.prototype.toggleRow.call(this, primary_record.id, true);
        }

        // revert old primary record to standard record, unless we dropped on the same record.
        if(old_primary_record && !(old_primary_record === primary_record)) {
            this.toggleFields(this.rowFields[old_primary_record.id], false);
        }
    },

    /**
     * Set primary record
     * @param {Model} model primary model
     */
    setPrimaryRecord: function(model) {
        var self = this;
        if (this.primaryRecord === model) {
            return;
        }

        // turn off events on the old primary record if applicable
        if (_.isFunction(this.primaryRecord,'off')) {
         this.primaryRecord.off('change',null,this);
         this.primaryRecord.off('change:name',null,this);
        }

        // get the new primary record wired up
        this.primaryRecord = model;
        this.updatePrimaryTitle(this.primaryRecord.get('name'));
        if (this.isPreviewOpen) {
            this.updatePreviewRecord(this.primaryRecord);
        }

        this.primaryRecord.on('change:name', function(model, value, options) {
            this.updatePrimaryTitle(value);
        }, this);

        this.primaryRecord.on('change', function(model){
            if (this.isPreviewOpen) {
                app.events.trigger('preview:close'); // either this or set a previewId on the model
                this.updatePreviewRecord(this.primaryRecord);
            }
        }, this);
    },

    /**
     * custom bindDataChange
     */
    bindDataChange: function() {
        this.collection.on('reset', function (coll) {
            this.setPrimaryRecord(coll.at(0));
            this.render();
        }, this);
    }
})
