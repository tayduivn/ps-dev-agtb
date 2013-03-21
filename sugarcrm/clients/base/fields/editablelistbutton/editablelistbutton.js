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
        var fieldsToValidate = this.view.getFields(this.module);
        this.view.clearValidationErrors();
        if (this.model.isValid(fieldsToValidate)) {
            if (!this.changed) {
                this.cancelEdit();
            }
            else {
                var self = this;
                this.model.save({}, {
                    success: function(model) {
                        this.changed = false;
                        self.view.toggleRow(model.id, false);
                    },
                    //Show alerts for this request
                    showAlerts: {
                        'process' : true,
                        'success': {
                            messages: app.lang.getAppString('LBL_RECORD_SAVED')
                        }
                    }
                });
            }
        }
    },
    cancelEdit: function() {
        this.changed = false;
        this.model.revertAttributes();
        this.view.toggleRow(this.model.id, false);
    },
    saveClicked: function(evt) {
        this.saveModel();
    },
    cancelClicked: function(evt) {
        this.cancelEdit();
    }
})
