({
    DUPLICATE_VIEW: 'duplicate',
    RECORD_VIEW: 'record',

    events:{
        'click .toggle-subview':'handleToggleClick'
    },

    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        _.bindAll(this);
        this.context.on("lead:convert:populate", this.populateRecordsFromLeads, this);
        this.context.on("lead:convert:"+this.meta.module+":show", this.handleShow, this);
        this.context.on("lead:convert:"+this.meta.module+":hide", this.handleHide, this);
        this.context.on("lead:convert:"+this.meta.module+":validate", this.runValidation, this);
        this.context.on("lead:convert:"+this.meta.module+":enable", this.handleEnablePanel, this);
        this.currentState = {
            activeView: this.DUPLICATE_VIEW,
            duplicateCount: 0,
            selectedId: null,
            selectedName: '',
            isComplete: false,
            isDirty: false
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
                self.updatePanelSubTitle();
                self.updateDuplicateMessage(self.duplicateView);
                if (self.duplicateView.collection.length === 0) {
                    self.toggleSubViews(this.RECORD_VIEW);
                }
            }, self);
            self.duplicateView.loadData();
        } else {
            self.toggleSubViews(this.RECORD_VIEW);
        }
    },

    insertDuplicateViewInPanel: function(moduleMeta) {
        var self = this,
            def = {
                'view':'duplicate-list',
                'context':{'module':moduleMeta.module}
        };

        this.duplicateView = this.insertViewInPanel(moduleMeta, this.DUPLICATE_VIEW, def);
        this.duplicateView.context.on('change:selection_model', this.selectDuplicate, self);
    },

    insertRecordViewInPanel: function(moduleMeta) {
        var def = {
            'view':'edit',
            'context':{'module':moduleMeta.module}
        };

        this.recordView = this.insertViewInPanel(moduleMeta, this.RECORD_VIEW, def);
    },

    insertViewInPanel:function (moduleMeta, contentType, def) {
        var self = this,
            context = self.context.getChildContext(def.context);

        context.prepare();
        context.set('limit', 3);

        var view = app.view.createView({
            context:context,
            name:def.view,
            module:moduleMeta.module,
            layout:self,
            id:def.id
        });

        this.$('#collapse' + moduleMeta.module).find('.' + contentType + 'View').append(view.el);
        view.render();

        return view;
    },

    handleShow: function() {
        this.showBody();
        this.showSubViewToggle();
    },

    showBody: function () {
        var panelBody = '#collapse' + this.meta.module;
        this.$(panelBody).collapse('show');
    },

    handleHide: function() {
        this.updatePanelHeader();
        this.hideSubViewToggle();
    },

    handleEnablePanel: function() {
        this.$('.accordion-heading').removeClass('disabled').addClass('enabled');
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
        this.markCompleted();
    },

    updatePanelHeader: function() {
        this.updatePanelTitle();
        this.updatePanelSubTitle();
    },

    //todo: translations
    updatePanelTitle: function() {
        var newTitle;

        if (this.currentState.isComplete) {
            newTitle = '<i class="icon-ok-sign icon-added"></i> ' + this.meta.moduleSingular + ' Associated:';
            if (!this.meta.required) {
                this.$('.optional').hide();
            }
        } else {
            newTitle = 'Associate ' + this.meta.moduleSingular;
            if (!this.meta.required) {
                this.$('.optional').show();
            }
        }
        this.$('.title').html(newTitle);
    },

    //todo: translations
    updatePanelSubTitle: function() {
        var newSubTitle;
        
        if (this.currentState.isComplete) {
            newSubTitle = this.currentState.selectedName;
        } else if (this.currentState.activeView === this.DUPLICATE_VIEW) {
            if (this.currentState.duplicateCount > 0) {
                newSubTitle = '> ' + this.currentState.duplicateCount + ' duplicates found';
            }
        } else if (this.currentState.activeView === this.RECORD_VIEW) {
            newSubTitle = '> Create New ' + this.meta.moduleSingular;
        } else {
            return;
        }
        
        this.$('.sub-title').html(newSubTitle);
    },

    hideSubViewToggle: function() {
        this.$('.subview-toggle').hide();
    },

    showSubViewToggle: function() {
        this.$('.subview-toggle').show();
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
    },
    
    toggleDuplicateView: function(show) {
        this.duplicateView.$el.parent().toggle(show);
        this.$('.show-record').toggle(show);
        if (show) {
            this.currentState.activeView = this.DUPLICATE_VIEW;
        }
        this.setDirty(false);
    },

    toggleRecordView: function(show) {
        this.recordView.$el.parent().toggle(show);
        if (this.currentState.duplicateCount > 0) {
            this.$('.show-duplicate').toggle(show);
        }
        if (show) {
            this.currentState.activeView = this.RECORD_VIEW;
        }
        this.setDirty(true);
    },

    updateDuplicateMessage: function(view) {
        var $foundDuplicatePlaceholder = this.$('.accordion-group[data-module=' + view.module + ']').find('.found-duplicate');
        $foundDuplicatePlaceholder.text(view.collection.length + ' duplicates found'); //todo translate
    },

    markCompleted: function() {
        this.currentState.isComplete = true;
        this.context.trigger("lead:convert:panel:update");
    },

    setDirty: function(isDirty) {
        this.currentState.isDirty = isDirty;
        this.context.trigger("lead:convert:panel:update");
    },

    runValidation: function(callback) {
        //dirty record views need validation - other scenarios do not
        if (this.currentState.activeView === this.RECORD_VIEW && this.currentState.isDirty) {
            //todo: implement validation - this is a placeholder
            var model = this.recordView.model;
            if (model.get('name') === 'fail' || model.get('first_name') === 'fail') {
                app.alert.show('failed_validation', {level:'error', title: 'Failed Validation', messages: 'Failed Validation', autoClose: true});
            } else {
                callback();
            }
        } else {
            callback();
        }
    },

    isComplete: function() {
       return (this.currentState.isComplete || this.currentState.isDirty);
    }
})