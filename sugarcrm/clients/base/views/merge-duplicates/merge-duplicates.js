({
    extendsFrom: 'BaselistView',
    events: {
        'click .show_extra' : 'showMore',
        'click .preview' : 'previewRecord',
    },
    MAX_RECORDS: 5, // the number of records we can merge, by fiat
    rowFields: {},
    primaryRecord: {},
    alternativeRecords: {},
    initialize: function(options) {
        var meta = app.metadata.getView(options.module, 'record'),
            visibleFields = [],
            hiddenFields = [],
            self = this;

        this.getPrimaryRecord(options.context);
        this.recordName = this.primaryRecord.attributes['name'] || '';

        _.each(meta.panels, function(panel) {
            var fields;

            fields = this.flattenFieldsets(panel.fields);

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

                if(isSimilar(field, self.primaryRecord, self.alternativeRecords)) {
                    hiddenFields.push(fieldMeta);
                }
                else {
                    visibleFields.push(fieldMeta);
                }
            }, this);
        }, this);
        options.meta = {
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
        }
        app.view.View.prototype.initialize.call(this, options);
        this.action = 'list';
    },
    /**
     * Display a Preview for the primary record
     */
    previewRecord: function() {
        var previewModels = [this.primaryRecord];        
        var previewCollection = app.data.createBeanCollection(this.primaryRecord.get('_module'), previewModels);
        this.context.trigger("preview:render", this.primaryRecord, previewCollection);
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
            fields = _.chain(fieldsets)
                .reject(fieldsetFilter)
                .union(fields)
                .value();

            // do we have any more fieldsets to squash?
            fieldsets = _.filter(fieldsets, fieldsetFilter);
        }
        return fields;
    },
    showMore: function(evt) {
        this.$(".col .extra").toggleClass('hide');
    },
    _render:function () {
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
        this.setPrimaryEdit();
    },
    setPrimaryEdit: function() {
        //TODO: Should store the primary record
        var primary_model = this.collection.models[0];
        this.context.set("primary_model", primary_model);
        if(primary_model) {
            this.toggleFields(this.rowFields[primary_model.id], true);
            //app.view.views.ListView.prototype.toggleRow.call(this, primary_model.id, true);
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

        if (context.has("primary_record")) {
            this.primaryRecord = this.get("primary_record");
        }
        else {
            this.primaryRecord = records[0];
        }

        this.alternativeRecords = _.reject(records, function(record) {
            return record.id == this.primaryRecord.id;
        }, this);
    }
})
