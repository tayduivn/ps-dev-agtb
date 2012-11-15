({
    events:{
        'click .toggle-subview':'handleToggleClick',
        'click [name=pick]': 'selectDuplicate'
    },

    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        _.bindAll(this);
        this.context.on("lead:convert:populate", this.populateRecordsFromLeads, this);

        this.currentState = {
            activeView: 'duplicate',
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
                if (self.duplicateView.collection.length == 0) {
                    self.toggleSubViews('record');
                }
            }, self);
            self.duplicateView.loadData();
        } else {
            self.toggleDuplicateView(false, false);
            self.toggleRecordView(true, false);
        }
    },

    insertDuplicateViewInPanel: function(moduleMeta) {
        var def = {
                'view':'list-singleselect',
                'context':{'module':moduleMeta.module}
        };

        this.duplicateView = this.insertViewInPanel(moduleMeta, 'duplicate', def);
    },

    insertRecordViewInPanel: function(moduleMeta) {
        var def = {
            'view':'edit',
            'context':{'module':moduleMeta.module}
        };

        this.recordView = this.insertViewInPanel(moduleMeta, 'record', def);
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

    handleToggleClick: function(event) {
        if (this.$(event.target).hasClass('show-duplicate')) {
            this.toggleSubViews('duplicate');
        } else if (this.$(event.target).hasClass('show-record')) {
            this.toggleSubViews('record');
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
            newTitle = '<i class="icon-ok-sign"></i> ' + this.meta.moduleSingular + ' Associated:';
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
        } else if (this.currentState.activeView === 'duplicate') {
            if (this.currentState.duplicateCount > 0) {
                newSubTitle = '> ' + this.currentState.duplicateCount + ' duplicates found';
            }
        } else if (this.currentState.activeView === 'record') {
            newSubTitle = '> Create New ' + this.meta.moduleSingular;
        } else {
            return;
        }
        
        this.$('.sub-title').html(newSubTitle);
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
        this.toggleDuplicateView(viewToShow === 'duplicate');
        this.toggleRecordView(viewToShow === 'record');
    },
    
    toggleDuplicateView: function(showView, showLink) {
        showLink = (_.isUndefined(showLink)) ? showView : showLink;
        this.duplicateView.$el.parent().toggle(showView);
        this.$('.show-record').toggle(showLink);
        if (showView) {
            this.currentState.activeView = 'duplicate';
        }
    },

    toggleRecordView: function(showView, showLink) {
        showLink = (_.isUndefined(showLink)) ? showView : showLink;
        this.recordView.$el.parent().toggle(showView);
        this.$('.show-duplicate').toggle(showLink);
        if (showView) {
            this.currentState.activeView = 'record';
        }
    },

    updateDuplicateMessage: function(view) {
        var $foundDuplicatePlaceholder = this.$('.accordion-group[data-module=' + view.module + ']').find('.found-duplicate');
        $foundDuplicatePlaceholder.text(view.collection.length + ' duplicates found'); //todo translate
    }
})