({
    DUPLICATE_VIEW: 'duplicate',
    RECORD_VIEW: 'record',

    STATUS_INIT: 'init',
    STATUS_DIRTY: 'dirty',
    STATUS_COMPLETE: 'complete',

    events:{
        'click .toggle-subview':'handleToggleClick'
    },

    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        _.bindAll(this);
        this.context.on("lead:convert:populate", this.handlePopulateRecords, this);
        this.context.on("lead:convert:"+this.meta.module+":show", this.handleShow, this);
        this.context.on("lead:convert:"+this.meta.module+":hide", this.handleHide, this);
        this.context.on("lead:convert:"+this.meta.module+":validate", this.runValidation, this);
        this.context.on("lead:convert:"+this.meta.module+":enable", this.handleEnablePanel, this);
        this.currentState = {
            activeView: this.DUPLICATE_VIEW,
            duplicateCount: 0,
            selectedId: null,
            selectedName: ''
        };
    },

    render:function () {
        app.view.View.prototype.render.call(this);
        this.initiateSubComponents(this.meta);
        this.meta.moduleSingular = this.recordView.moduleSingular;
        this.updatePanelHeader();
    },

    /**
     * Add sub-views defined by the convert metadata to the view
     */
    initiateSubComponents:function (moduleMeta) {
        var self = this;

        self.insertDuplicateViewInPanel(moduleMeta);
        self.insertRecordViewInPanel(moduleMeta);

        if (moduleMeta.duplicateCheck) {
            self.duplicateView.collection.on("reset", function(){
                self.currentState.duplicateCount = self.duplicateView.collection.length;
                self.updatePanelHeader();
                if (self.duplicateView.collection.length === 0) {
                    self.toggleSubViews(this.RECORD_VIEW);
                }
            }, self);
        } else {
            self.toggleSubViews(this.RECORD_VIEW);
        }
    },

    /**
     * Inserts the duplicate list view into module panel based on metadata
     */
    insertDuplicateViewInPanel: function(moduleMeta) {
        var context = this.context.getChildContext({
            'module': moduleMeta.module,
            'forceNew': true,
            'dupelisttype': 'dupecheck-list-select'
        });
        context.prepare();

        this.duplicateView = app.view.createLayout({
            context: context,
            name: 'dupecheck',
            module: context.module
        });

        this.$('.' + this.DUPLICATE_VIEW + 'View').append(this.duplicateView.el);
        this.duplicateView.render();
        this.duplicateView.context.on('change:selection_model', this.selectDuplicate, this);
        this.duplicateView.validationStatus = this.STATUS_INIT;
    },

    /**
     * Insert the create/record view into the module panel based on metadata
     */
    insertRecordViewInPanel: function(moduleMeta) {
       var context = this.context.getChildContext({
           'module': moduleMeta.module,
           forceNew: true,
           create: true
       });
       context.prepare();

       this.recordView = app.view.createView({
            context: context,
            name: 'create',
            module: context.module,
            layout: this
        });

        this.$('.' +  this.RECORD_VIEW + 'View').append(this.recordView.el);
        this.recordView.enableHeaderPane = false;
        this.recordView.render();

        this.recordView.validationStatus = this.STATUS_DIRTY;
    },

    handleShow: function() {
        this.$('.header').addClass('active');
        this.showBody();
        this.showSubViewToggle();
    },

    showBody: function () {
        var panelBody = '#collapse' + this.meta.module;
        this.$(panelBody).collapse('show');
    },

    handleHide: function() {
        this.$('.header').removeClass('active');
        this.updatePanelHeader();
        this.hideSubViewToggle();
    },

    handleEnablePanel: function() {
        this.$('.header').removeClass('disabled').addClass('enabled');
    },

    handleToggleClick: function(event) {
         if (this.$(event.target).hasClass('show-duplicate')) {
            this.toggleSubViews(this.DUPLICATE_VIEW);
        } else if (this.$(event.target).hasClass('show-record')) {
            this.toggleSubViews(this.RECORD_VIEW);
        }
        event.stopPropagation();
    },

    selectDuplicate: function(e) {
       var selectedModel = e.changed.selection_model;

        this.currentState.selectedId = selectedModel.get('id');
        this.currentState.selectedName = selectedModel.get('name');
        this.setStatus(this.STATUS_DIRTY);
    },

    updatePanelHeader: function() {
        this.updatePanelTitle();
        this.updatePanelSubTitle();
    },

    updatePanelTitle: function() {
        var newTitle;

        if (this.getStatus() === this.STATUS_COMPLETE) {
            this.$('.step-circle-right').addClass('complete');
            newTitle = app.lang.get(
                'LBL_CONVERT_MODULE_ASSOCIATED',
                this.module,
                {'moduleName': this.meta.moduleSingular}
            );
            if (!this.meta.required) {
                this.$('.optional').hide();
            }
        } else {
            this.$('.step-circle-right').removeClass('complete');
            newTitle = app.lang.get(
                'LBL_CONVERT_ASSOCIATE_MODULE',
                this.module,
                {'moduleName': this.meta.moduleSingular}
            );
            if (!this.meta.required) {
                this.$('.optional').show();
            }
        }
        this.$('.title').text(newTitle);
    },

    updatePanelSubTitle: function() {
        var newSubTitle, translatedString;

        if (this.getStatus() === this.STATUS_COMPLETE) {
            if (this.currentState.activeView === this.DUPLICATE_VIEW) {
                newSubTitle = this.currentState.selectedName;
            } else {
                newSubTitle = this.getDisplayName(this.recordView.model);
            }

        } else if (this.currentState.activeView === this.DUPLICATE_VIEW) {
            if (this.currentState.duplicateCount > 0) {
                translatedString = app.lang.get(
                    'LBL_CONVERT_DUPLICATES_FOUND',
                    this.module,
                    {'duplicateCount': this.currentState.duplicateCount}
                );
                newSubTitle = ' ' + translatedString;
            }
        } else if (this.currentState.activeView === this.RECORD_VIEW) {
            translatedString = app.lang.get(
                'LBL_CONVERT_CREATE_NEW',
                this.module,
                {'moduleName': this.meta.moduleSingular}
            );
            newSubTitle = ' ' + translatedString;
        } else {
            return;
        }

        this.$('.sub-title').text(newSubTitle);
    },

    getDisplayName: function(model) {
        var moduleFields = app.metadata.getModule(this.meta.module).fields,
            displayName = '';

        if (moduleFields.name && moduleFields.name.fields) {
            _.each(moduleFields.name.fields, function(field) {
                if (model.has(field)) {
                    displayName += model.get(field) + ' ';
                }
            });
        } else if (moduleFields.name) {
            displayName = model.get('name');
        }
        return displayName;
    },

    hideSubViewToggle: function() {
        this.$('.subview-toggle').hide();
    },

    showSubViewToggle: function() {
        this.$('.subview-toggle').show();
    },

    handlePopulateRecords: function(leadModel) {
        this.populateRecordsFromLeads(leadModel);
        this.duplicateView.context.trigger("dupecheck:fetch:fire", this.recordView.model);
    },

    populateRecordsFromLeads:function (leadModel) {
        var self = this;
        _.each(self.meta.fieldMapping, function (sourceField, targetField) {
            if (leadModel.has(sourceField)) {
                self.recordView.model.set(targetField, leadModel.get(sourceField));
            }
        });
    },

    toggleSubViews: function(viewToShow) {
        this.toggleDuplicateView(viewToShow === this.DUPLICATE_VIEW);
        this.toggleRecordView(viewToShow === this.RECORD_VIEW);
        this.updatePanelHeader();
        this.context.trigger("lead:convert:panel:update");
    },

    toggleDuplicateView: function(show) {
        this.duplicateView.$el.parent().toggle(show);
        this.$('.show-record').toggle(show);
        if (show) {
            this.currentState.activeView = this.DUPLICATE_VIEW;
        }
    },

    toggleRecordView: function(show) {
        this.recordView.$el.parent().toggle(show);
        if (this.currentState.duplicateCount > 0) {
            this.$('.show-duplicate').toggle(show);
        }
        if (show) {
            this.currentState.activeView = this.RECORD_VIEW;
        }
    },

    runValidation: function(callback, force) {
        var force = force || false;
        if (this.currentState.activeView === this.DUPLICATE_VIEW) {
            //mark completed if a value is selected
            if (this.currentState.selectedId !== null) {
                this.setStatus(this.STATUS_COMPLETE);
                callback();
            } else if (!this.meta.required || !force) {
                callback();
            } else {
                var title = app.lang.get('LBL_CONVERT_FAILED_VALIDATION_TITLE', 'Leads');
                var message = app.lang.get('LBL_CONVERT_FAILED_VALIDATION_MESSAGE', 'Leads');
                app.alert.show('failed_validation', {level:'error', title: title, messages: message, autoClose: true});
            }
        } else {
            var view = this.recordView,
                model = view.model;

            if (model.isValid(view.getFields(view.module))) {
                this.setStatus(this.STATUS_COMPLETE);
                callback();
           }
        }
    },

    getStatus: function() {
        if (this.currentState.activeView === this.DUPLICATE_VIEW) {
            if (this.duplicateView && this.duplicateView.validationStatus) {
                return this.duplicateView.validationStatus;
            } else {
                return this.STATUS_INIT;
            }
        } else {
            if (this.recordView && this.recordView.validationStatus) {
                return this.recordView.validationStatus;
            } else {
                return this.STATUS_DIRTY;
            }
        }
    },

    setStatus: function(status) {
        if (this.currentState.activeView === this.DUPLICATE_VIEW) {
            this.duplicateView.validationStatus = status;
        } else {
            this.recordView.validationStatus = status;
        }
        this.context.trigger("lead:convert:panel:update");
    },

    isDirtyOrComplete: function() {
       return (this.getStatus() === this.STATUS_COMPLETE || this.getStatus() === this.STATUS_DIRTY);
    },

    getAssociatedModel: function() {
        var associatedModel;

        if (this.getStatus() !== this.STATUS_COMPLETE) {
            return null;
        }

        if (this.currentState.activeView === this.DUPLICATE_VIEW) {
            associatedModel = new Backbone.Model();
            associatedModel.set('id', this.currentState.selectedId);
            return associatedModel;
        } else {
            return this.recordView.model;
        }
    }
})