({
    extendsFrom: 'BaselistView',
    events: {
        'click .show_extra' : 'showMore'
    },
    rowFields: {},
    initialize: function(options) {

        var meta = app.metadata.getView(options.module, 'record'),
            visibleFields = [],
            hiddenFields = [];
        _.each(meta.panels, function(panel) {

            _.each(panel.fields, function(field, index){
                panel.fields[index] = {
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
            }, this);

            if(panel.hide) {
                hiddenFields = _.union(hiddenFields, panel.fields);
            } else {
                visibleFields = _.union(visibleFields, panel.fields);
            }
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
    }
})
