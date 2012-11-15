({
    DUPLICATE_VIEW: 'duplicate',
    RECORD_VIEW: 'record',

    events:{
        'click .toggle-subview':'handleToggleClick',
        'click .pick': 'selectDuplicate'
    },

    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        _.bindAll(this);
        this.context.on("lead:convert:populate", this.populateRecordsFromLeads, this);
        this.context.on("lead:convert:"+this.meta.module+":show", this.handleShow, this);
        this.context.on("lead:convert:"+this.meta.module+":hide", this.handleHide, this);

        this.currentState = {
            activeView: this.DUPLICATE_VIEW,
            duplicateCount: 0,
            selectedId: null,
            selectedName: '',
            isComplete: false
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
        var def = {
                'view':'list-singleselect',
                'context':{'module':moduleMeta.module}
        };

        this.duplicateView = this.insertViewInPanel(moduleMeta, this.DUPLICATE_VIEW, def);
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

    handleToggleClick: function(event) {
        if (this.$(event.target).hasClass('show-duplicate')) {
            this.toggleSubViews(this.DUPLICATE_VIEW);
        } else if (this.$(event.target).hasClass('show-record')) {
            this.toggleSubViews(this.RECORD_VIEW);
        }
        event.stopPropagation();
    },

    selectDuplicate: function(e) {
        var $selectedRadio = this.$(e.target),
            recordId = $selectedRadio.val(),
            selectedModel;

        this.currentState.selectedId = recordId;
        selectedModel = this.duplicateView.collection.get(recordId);
        this.currentState.selectedName = selectedModel.get('name');
        this.currentState.isComplete = true;
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

        //field mappings: copy over data according to the metadata field mapping
        _.each(self.meta.fieldMapping, function (sourceField, targetField) {
            if (leadModel.has(sourceField)) {
                self.recordView.model.set(targetField, leadModel.get(sourceField));
            }
        });
    },

    toggleSubViews: function(viewToShow) {
        this.toggleDuplicateView(viewToShow === this.DUPLICATE_VIEW);
        this.toggleRecordView(viewToShow === this.RECORD_VIEW);
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

    updateDuplicateMessage: function(view) {
        var $foundDuplicatePlaceholder = this.$('.accordion-group[data-module=' + view.module + ']').find('.found-duplicate');
        $foundDuplicatePlaceholder.text(view.collection.length + ' duplicates found'); //todo translate
    }
})