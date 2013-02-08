({
    extendsFrom: 'BaselistView',
    isPreviewOpen: false,
    events: {
        'click .show_extra' : 'showMore',
        'click .preview' : 'previewRecord'
    },
    MAX_RECORDS: 5, // the number of records we can merge, by fiat
    mergeFields: [], // list of fields to generate the metadata on the fly
    rowFields: {},
    primaryRecord: {},
    initialize: function(options) {
        var meta = app.metadata.getView(options.module, 'record'),
            mergeCollection = options.context.get('collection'),
            records = options.context.get("selectedDuplicates"),
            primary,
            ids;

        // bomb out if we don't have between 2 and MAX_RECORDS
        if (!records.length || records.length < 2 || records.length > this.MAX_RECORDS) {
            app.alert.show('invalid-record-count',{
                level: 'error',
                messages: 'Invalid number of records passed.',
                autoClose: true
            });
            return;
        }

        // standardize primary record from list of records,
        // and put primary at the beginning of records.
        // this is useful primarily to know which record will be the primary
        // in the collection to be pulled later. We do not use the input models
        primary = (options.context.has("primaryRecord")) ?
            _.findWhere(records,{id: options.context.get("primaryRecord").id}) :
            records[0];
        records = [primary].concat(_.without(records, primary));
        this.primaryRecord = primary;
        this.recordName = this.primaryRecord.get('name') || '';
        
        // these are the fields we'll need to pull our records
        this.mergeFields = _.chain(meta.panels)
            .map(function(panel) {return this.flattenFieldsets(panel.fields); }, this)
            .flatten()
            .reject(function(field) { return !field.name; })
            .value();

        // enforce the order of the ids so that primaryRecord always appears first
        // and only retrieve the records specified
        ids = (_.pluck(records,'id'));
        if (mergeCollection) {
            mergeCollection.fields = this.mergeFields; // to make sure we pull all fields we need.
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
        var self = this, alternativeModelNames = [];
        var alternativeModels = this.collection.without(this.primaryRecord); 
        _.each(alternativeModels, function(model) {
            alternativeModelNames.push(model.get('name') || "");
        });
        app.alert.show('merge_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('LBL_MERGE_DUPLICATES_CONFIRM') + " "+ alternativeModelNames.join(", ") + ". "+ app.lang.get('LBL_MERGE_DUPLICATES_PROCEED'),
            onConfirm: function() {
                self.primaryRecord.save({}, {
                    success: function() {
                        _.each(alternativeModels, function(model) {
                            model.destroy();
                        }); 
                        self.context.trigger("drawer:hide");
                        if (self.context.parent) {
                            self.context.parent.trigger("drawer:hide");
                        }
                    },
                    error: function() {
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
     * Display a Preview for the primary record
     */
    previewRecord: function(togglePreview) {
        if(_.isUndefined(togglePreview) || togglePreview) {
            if(this.isPreviewOpen) {
                app.events.trigger("preview:close");
                this.isPreviewOpen = false;
                return;
            } 
        }         
        var previewModel = this.primaryRecord;
        var previewModels = [previewModel];        
        var previewCollection = app.data.createBeanCollection(previewModel.get('_module') || previewModel.module, previewModels);
        app.events.trigger("preview:render", previewModel, previewCollection, false);
        this.isPreviewOpen = true;
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
                hiddenFields.push(fieldMeta);
            }
            else {
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
     * Display a Preview for the primary record
     */
    previewRecord: function(togglePreview) {
        if(_.isUndefined(togglePreview) || togglePreview) {
            if(this.isPreviewOpen) {
                app.events.trigger("preview:close");
                this.isPreviewOpen = false;
                return;
            } 
        }         
        var previewModel = this.primaryRecord;
        var previewModels = [previewModel];        
        var previewCollection = app.data.createBeanCollection(previewModel.get('_module') || previewModel.module, previewModels);
        app.events.trigger("preview:render", previewModel, previewCollection, false);
        this.isPreviewOpen = true;
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
    showMore: function(evt) {
        this.$(".col .extra").toggleClass('hide');
    },
    _render:function () {
        this.meta = this.generateMetadata(this.mergeFields, this.collection, this.primaryRecord);
        app.view.views.BaselistView.prototype._render.call(this);
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
        this.setPrimaryEdit(this.primaryRecord);
        this.$('[rel="tooltip"]').tooltip();
        this.setSortable();
    },
    setSortable: function() {
        this.$(".ui-sortable").sortable();
        this.$(".ui-sortable").disableSelection();    	
    },
    /**
     * Do what we need to do when the primary record is set
     * @param {Model} primary the record representing the new primary model
     */
    setPrimaryEdit: function(primary) {
        // make sure we get the model in the collection, with all fields in it.
        var primary_record = this.collection.get(primary.id);

        if(primary_record) {
            this.setPrimaryRecord(primary_record);            
            this.context.set("primary_record", primary_record);
            this.toggleFields(this.rowFields[primary_record.id], true);
            //app.view.views.ListView.prototype.toggleRow.call(this, primary_record.id, true);
        }
    },
    getPrimaryRecord: function(context) {
        var records = context.get("selectedDuplicates");

        // bomb out if we don't have between 2 and MAX_RECORDS
        if (!records.length || records.length < 2 || records.length > this.MAX_RECORDS) {
            app.alert.show('invalid-record-count',{
                level: 'error',
                messages: 'Invalid number of records passed.',
                autoClose: true
            });
            return;
        }

        if (!context.has("primary_record")) {
            context.set("primary_record",records[0]);
        }
        this.setPrimaryRecord(context.get("primary_record"));
        
        this.alternativeRecords = _.reject(records, function(record) {
            return record.id == this.primaryRecord.id;
        }, this);
    },
    setPrimaryRecord: function(model) {
        this.primaryRecord = model;
        this.primaryRecord.on("change", function(){
            app.events.trigger('preview:close');
            this.previewRecord(false);
        }, this);  
        this.recordName = this.primaryRecord.get('name') || '';        
    }
})
