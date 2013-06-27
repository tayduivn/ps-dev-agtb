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
        if (!isValid) return;
        if (!this.changed) {
            this.cancelEdit();
            return;
        }

        var self = this,
            options = {
                success: function() {
                    self.model.save({}, {
                        success: function(model) {
                            self.changed = false;
                            self.view.toggleRow(model.id, false);
                        },
                        //Show alerts for this request
                        showAlerts: {
                            'process': true,
                            'success': {
                                messages: app.lang.get('LBL_RECORD_SAVED', self.module)
                            }
                        },
                        relate: self.model.link ? true : false
                    });
                }
        };

        options = _.extend({}, options, self.getCustomSaveOptions(options));

        callbacks = {
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
        var fieldsToValidate = this.view.getFields(this.module);
        this.view.clearValidationErrors();
        this.model.doValidate(fieldsToValidate, _.bind(this._validationComplete, this));
    },
    cancelEdit: function() {
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
    }
})
