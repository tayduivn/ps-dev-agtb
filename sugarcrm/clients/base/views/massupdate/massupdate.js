({
    events: {
        'click .add' : 'addItem',
        'click .remove' : 'removeItem',
        'click .btn[name=update_button]' : 'saveClicked',
        'click .btn.cancel_button' : 'cancelClicked'
    },
    visible: false,
    fieldOptions: null,
    fieldValues: null,
    defaultOption: null,
    fieldPlaceHolderTag: '[name=fieldPlaceHolder]',
    initialize: function(options) {
        this.fieldValues = [{}];
        this.setMetadata(options);
        app.view.View.prototype.initialize.call(this, options);
        this.setDefault();

        this.delegateListFireEvents();
        this.before('render', this.isVisible);
    },
    delegateListFireEvents: function() {
        this.layout.on("list:massupdate:fire", this.show, this);
        this.layout.on("list:massaction:hide", this.hide, this);
        this.layout.on("list:massdelete:fire", this.confirmDelete, this);
        this.layout.on("list:massexport:fire", this.massExport, this);
    },
    setMetadata: function(options) {
        options.meta.panels = options.meta.panels || [{fields:[]}];
        if(_.size(options.meta.panels[0].fields) === 0) {
            var moduleMetadata = app.metadata.getModule(options.module),
                massFields = [];
            _.each(moduleMetadata.fields, function(field){
                if(field.massupdate) {
                    var cloneField = app.utils.deepCopy(field);
                    cloneField.label = field.label || field.vname;
                    if(!cloneField.label) delete cloneField.label;
                    //TODO: Remove hack code for teamset after metadata return correct team type
                    if(cloneField.name === 'team_name') {
                        cloneField.type = 'teamset';
                        cloneField.css_class = 'span9';
                        cloneField = {
                            type: 'fieldset',
                            name: 'team_name',
                            label: cloneField.label,
                            css_class : 'row-fluid',
                            fields: [
                                cloneField,
                                {
                                    'name' : 'team_name_type',
                                    'type' : 'bool',
                                    'text' : 'LBL_SELECT_APPEND_TEAMS',
                                    'css_class' : 'span3'
                                }
                            ]
                        };
                    }
                    if(cloneField.type === 'bool') {
                        cloneField.type = 'enum';
                        cloneField.options = 'checkbox_dom';
                    }
                    massFields.push(cloneField);
                }
            });
            options.meta.panels[0].fields = massFields;
        }
    },
    _render: function() {
        var result = app.view.View.prototype._render.call(this),
            self = this;

        if (this.$(".select2.mu_attribute")) {
            this.$(".select2.mu_attribute")
                .select2({
                    width: '100%',
                    minimumResultsForSearch: 5
                })
                .on("change", function(evt) {
                    var $el = $(this),
                        name = $el.select2('val'),
                        index = $el.data('index');
                    var option = _.find(self.fieldOptions, function(field){
                        return field.name == name;
                    });
                    self.replaceUpdateField(option, index);
                    self.placeField($el);
                });
            this.$(".select2.mu_attribute").each(function(){
                self.placeField($(this));
            });
        }

        if(this.fields.length == 0) {
            this.hide();
        }
        return result;
    },
    isVisible: function() {
        return this.visible;
    },
    placeField: function($el) {
        var name = $el.select2('val'),
            index = $el.data('index'),
            fieldEl = this.getField(name).$el;

        if($el.not(".disabled") && fieldEl) {
            var holder = this.$(this.fieldPlaceHolderTag + "[index=" + index + "]");
            this.$("#fieldPlaceHolders").append(holder.children());
            holder.html(fieldEl);
        }
    },
    addItem: function(evt) {
        if(!$(evt.currentTarget).hasClass("disabled")) {
            this.addUpdateField();
            // this will not be called in an async process so no need to
            // check for the view to be disposed
            this.render();
        }
    },
    removeItem: function(evt) {
        var index = $(evt.currentTarget).data('index');
        this.removeUpdateField(index);
        // this will not be called in an async process so no need to
        // check for the view to be disposed
        this.render();
    },
    addUpdateField: function() {
        this.fieldValues.splice(this.fieldValues.length - 1, 0, this.defaultOption);
        this.defaultOption = null;
        this.setDefault();
    },
    removeUpdateField: function(index) {
        var fieldValue = this.fieldValues[index];
        if(fieldValue) {
            if(fieldValue.name) {
                this.model.unset(fieldValue.name);
                this.fieldValues.splice(index, 1);
            } else {
                //last item should be empty
                var removed = this.fieldValues.splice(index - 1, 1);
                this.defaultOption = removed[0];
            }
            this.setDefault();
        }
    },
    replaceUpdateField: function(selectedOption, targetIndex) {
        var fieldValue = this.fieldValues[targetIndex];

        if(fieldValue.name) {
            this.model.unset(fieldValue.name);
            this.fieldOptions.push(fieldValue);
            this.fieldValues[targetIndex] = selectedOption;
        } else {
            this.model.unset(this.defaultOption.name);
            this.fieldOptions.push(this.defaultOption);
            this.defaultOption = selectedOption;
        }
    },
    setDefault: function() {
        var assignedValues = _.pluck(this.fieldValues, 'name');
        if(this.defaultOption) {
            assignedValues = assignedValues.concat(this.defaultOption.name);
        }
        //remove the attribute options that has been already assigned
        this.fieldOptions = (this.meta) ? _.reject(_.flatten(_.pluck(this.meta.panels, 'fields')), function(field){
            return (field) ? _.contains(assignedValues, field.name) : false;
        }) : [];
        //set first item as default
        this.defaultOption = this.defaultOption || this.fieldOptions.splice(0, 1)[0];
    },
    getMassUpdateModel: function(module) {
        var massModel = this.context.get("mass_collection");
        return massModel ? _.extend(massModel, {
            sync: function(default_method, model, options) {
                var callbacks = {
                        success: function(data, response) {
                            options.success(model, data, response);
                        },
                        error: options.error,
                        complete: options.complete
                    },
                    method = options.method || this.defaultMethod,
                    data = this.getAttributes(options.attributes),
                    url = app.api.buildURL(module, this.module, data, options.params);
                app.api.call(method, url, data, callbacks);
            },
            defaultMethod: 'update',
            module: 'MassUpdate',
            getAttributes: function(attributes) {
                return {
                    massupdate_params: _.extend({
                        'uid' : (this.entire) ? null : this.pluck('id'),
                        'entire' : this.entire,
                        'filter' : (this.entire) ? this.filterDef : null
                    }, attributes)
                };
            }
        }) : null;
    },
    confirmDelete: function(evt) {
        var self = this;
        this.hideAll();
        app.alert.show('delete_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('NTC_DELETE_CONFIRMATION_MULTIPLE'),
            onConfirm: function() {
                var massUpdate = self.getMassUpdateModel(self.module);
                var lastSelectedModels = _.clone(massUpdate.models);
                if(massUpdate) {
                    massUpdate.fetch({
                        //Don't show alerts for this request
                        showAlerts: false,
                        method: 'delete',
                        error: function() {
                            app.alert.show('error_while_mass_update', {level:'error', title: app.lang.getAppString('ERR_INTERNAL_ERR_MSG'), messages: app.lang.getAppString('ERR_HTTP_500_TEXT'), autoClose: true});
                        },
                        success: function(data, response) {
                            if(response.status == 'done') {
                                //TODO: Since self.layout.trigger("list:search:fire") is deprecated by filterAPI,
                                //TODO: Need trigger for fetching new record list
                                app.alert.show('massupdate_success_notice', {level: 'success', title: app.lang.getAppString('LBL_DELETED'), autoClose: true});
                                self.layout.context.reloadData({showAlerts: false});
                            } else if (response.status == 'queued') {
                                app.alert.show('jobqueue_notice', {level: 'success', title: app.lang.getAppString('LBL_MASS_UPDATE_JOB_QUEUED'), autoClose: true});
                            }
                            self.layout.trigger("list:record:deleted", lastSelectedModels);
                        }
                    });
                }
            }
        });
    },
    massExport: function(evt) {
        this.hideAll();
        var massExport = this.context.get("mass_collection");
        var exportOptions;

        if (massExport) {
            app.alert.show('massexport_loading', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_LOADING')});

            // we need to get our filter cleaned up.
            exportOptions = app.data.parseOptionsForSync("read", massExport).params;

            app.api['export']({
                    module: this.module,
                    uid: (massExport.entire) ? null : massExport.pluck('id'),
                    entire: massExport.entire,
                    filter: (massExport.entire) ? exportOptions.filter : null
                },
                this.$el,
                {
                    complete: function(data) {
                        app.alert.dismiss('massexport_loading');
                    }
                });
        }
    },
    save: function() {
        var massUpdate = this.getMassUpdateModel(this.module),
            attributes = this.getAttributes(),
            self = this;

        this.once('massupdate:validation:complete', function(validate) {
            var errors = validate.errors,
                emptyValues = validate.emptyValues,
                confirmMessage = app.lang.getAppString('LBL_MASS_UPDATE_EMPTY_VALUES');

            this.$(".fieldPlaceHolder .error").removeClass("error");
            this.$(".fieldPlaceHolder .help-block").hide();

            if(_.isEmpty(errors)) {
                confirmMessage += '<br>[' + emptyValues.join(',') + ']<br>' + app.lang.getAppString('LBL_MASS_UPDATE_EMPTY_CONFIRM') + '<br>';
                if(massUpdate) {
                    var fetchMassupdate = _.bind(function() {
                        var successMessages = this.buildSaveSuccessMessages(massUpdate);
                        massUpdate.fetch({
                            //Show alerts for this request
                            showAlerts: true,
                            attributes: attributes,
                            error: function() {
                                app.alert.show('error_while_mass_update', {level:'error', title: app.lang.getAppString('ERR_INTERNAL_ERR_MSG'), messages: app.lang.getAppString('ERR_HTTP_500_TEXT'), autoClose: true});
                            },
                            success: function(data, response) {
                                self.hide();
                                if(response.status == 'done') {
                                    app.alert.show('massupdate_success_notice', {level: 'success', messages: successMessages[response.status], autoClose: true});
                                    //TODO: Since self.layout.trigger("list:search:fire") is deprecated by filterAPI,
                                    //TODO: Need trigger for fetching new record list
                                    self.layout.collection.fetch({
                                        //Don't show alerts for this request
                                        showAlerts: false,
                                        // Boolean coercion.
                                        relate: !!self.layout.collection.link
                                    });
                                } else if(response.status == 'queued') {
                                    app.alert.show('jobqueue_notice', {level: 'success', messages: successMessages[response.status], autoClose: true});
                                }
                            }
                        });
                    }, this);
                    if(emptyValues.length == 0) {
                        fetchMassupdate.call(this);
                    } else {
                        app.alert.show('empty_confirmation', {
                            level: 'confirmation',
                            messages: confirmMessage,
                            onConfirm: fetchMassupdate
                        });
                    }
                }
            } else {
                this.handleValidationError(errors);
            }
        }, this);

        this.checkValidationError();
    },

    /**
     * Build dynamic success messages to be displayed if the API call is successful
     * This is overridden by massaddtolist view which requires different success messages
     *
     * @param massUpdateModel - contains the attributes of what records are being updated (used by override in massaddtolist)
     */
    buildSaveSuccessMessages: function(massUpdateModel) {
        return {
            done: app.lang.getAppString('LBL_MASS_UPDATE_SUCCESS'),
            queued: app.lang.getAppString('LBL_MASS_UPDATE_JOB_QUEUED')
        };
    },

    /**
     * By default attributes are retrieved directly off the model, but broken out to allow for manipulation before handing off to the API
     */
    getAttributes: function() {
        return this.model.attributes;
    },

    checkValidationError: function() {
        var self = this,
            emptyValues = [],
            errors = {},
            validator = {},
            fields = _.initial(this.fieldValues).concat(this.defaultOption),
            i = 0;

        var fieldsToValidate = _.filter(fields, function(f) {
            return f.name;
        });

        if (_.size(fieldsToValidate)) {
            _.each(fieldsToValidate, function(field) {
                i++;
                validator = {};
                validator[field.name] = field;
                field.required = (_.isBoolean(field.required) && field.required) || (field.required && field.required == 'true') || false;

                var value = this.model.get(field.name);
                if (!value) {
                    emptyValues.push(app.lang.get(field.label, this.model.module));
                    this.model.set(field.name, '', {silent: true});
                    if (field.id_name) {
                        this.model.set(field.id_name, '', {silent: true});
                    }
                }
                this.model._doValidate(validator, errors, function(didItFail, fields, errors, callback) {
                    if (i === _.size(fieldsToValidate)) {
                        self.trigger('massupdate:validation:complete', {
                            errors: errors,
                            emptyValues: emptyValues
                        });
                    }
                });
            }, this);
        } else {
            this.trigger('massupdate:validation:complete', {
                errors: errors,
                emptyValues: emptyValues
            });
        }

        return;
    },
    handleValidationError: function(errors) {
        var self = this;
        _.each(errors, function (fieldErrors, fieldName) {
            var fieldEl = self.getField(fieldName).$el,
                errorEl = fieldEl.find(".help-block");
            fieldEl.addClass("error");
            if(errorEl.length == 0) {
                errorEl = $("<span>").addClass("help-block");
                errorEl.appendTo(fieldEl);
            }
            errorEl.show().html("");
            _.each(fieldErrors, function (errorContext, errorName) {
                errorEl.append(app.error.getErrorString(errorName, errorContext));
            });
        });
    },
    show: function() {
        this.hideAll();
        this.visible = true;
        this.defaultOption = null;
        this.model.clear();
        this.setDefault();

        var massModel = this.context.get("mass_collection");
        massModel.off("add remove reset", null, this);
        massModel.on("add remove reset", this.setDisabled, this);

        // show will be called only on context.trigger("list:massupdate:fire").
        // therefore this should never be called in a situation in which
        // the view is disposed.
        this.$el.show();
        this.render();
    },
    /**
     * Hide all views that make up the list mass action section (ie. massupdate, massaddtolist)
     */
    hideAll: function() {
        this.layout.trigger("list:massaction:hide");
    },
    hide: function() {
        this.visible = false;
        this.$el.hide();
    },
    setDisabled: function() {
        var massUpdate = this.getMassUpdateModel(this.module);
        if(massUpdate.length == 0) {
            this.$(".btn[name=update_button]").addClass("disabled");
        } else {
            this.$(".btn[name=update_button]").removeClass("disabled");
        }
    },
    saveClicked: function(evt) {
        if(this.$(".btn[name=update_button]").hasClass("disabled") === false) {
            this.save();
        }
    },
    cancelClicked: function(evt) {
        this.hide();
    },
    unbindData: function() {
        var massModel = this.context.get("mass_collection");
        if (massModel) {
            massModel.off(null, null, this);
        }
        app.view.View.prototype.unbindData.call(this);
    },
    unbind: function() {
        this.$(".select2.mu_attribute").select2('destroy');
        app.view.View.prototype.unbind.call(this);
    }
})
