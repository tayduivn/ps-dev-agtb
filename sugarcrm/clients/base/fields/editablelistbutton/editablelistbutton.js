({
    events: {
        'click [name=inline-save]' : 'saveClicked',
        'click [name=inline-cancel]' : 'cancelClicked'
    },
    extendsFrom: 'ButtonField',
    initialize: function(options) {
        app.view.fields.ButtonField.prototype.initialize.call(this, options);
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
    saveModel: function() {
        if(!this.changed) {
            this.cancelEdit();
        } else {
            var self = this;
            app.alert.dismiss('record-saved');
            app.alert.show('save_list_record', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_SAVING')});
            this.model.save({}, {
                fieldsToValidate: this.view.getFields(this.module),
                success: function(model) {
                    this.changed = false;
                    app.alert.dismiss('save_list_record');
                    self.view.toggleRow(model.id, false);
                }
            });
        }
    },
    cancelEdit: function() {
        this.changed = false;
        if (this.model.isDirty()) {
            this.model.revertAttributes();
        }
        this.view.toggleRow(this.model.id, false);
    },
    saveClicked: function(evt) {
        this.saveModel();
    },
    cancelClicked: function(evt) {
        this.cancelEdit();
    }
})
