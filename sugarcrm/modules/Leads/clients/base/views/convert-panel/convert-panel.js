({
    events:{
        'click .toggle-subview':'handleToggleClick',
        'click [name=pick]': 'selectDuplicate'
    },

    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        _.bindAll(this);
        this.context.on("lead:convert:populate", this.populateRecordsFromLeads, this);
    },

    render:function () {
        app.view.View.prototype.render.call(this);
        this.initiateSubComponents(this.meta);
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
      //  this.$( '#collapse' + this.meta.module).css('height', 'auto');
        if (this.$(event.target).hasClass('show-duplicate')) {
            this.toggleSubViews('duplicate');
        } else if (this.$(event.target).hasClass('show-record')) {
            this.toggleSubViews('record');
        }
    },

    //TODO: get this working again
    selectDuplicate: function(e) {
        var $selectedRadio = this.$(e.target),
            recordId = $selectedRadio.val(),
            module = $selectedRadio.attr('data-module'),
            subModel = this.context.convertModel.getSubModel(module);

        subModel.set('id', recordId);
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
    },

    toggleRecordView: function(showView, showLink) {
        showLink = (_.isUndefined(showLink)) ? showView : showLink;
        this.recordView.$el.parent().toggle(showView);
        this.$('.show-duplicate').toggle(showLink);
    },

    updateDuplicateMessage: function(view) {
        var $foundDuplicatePlaceholder = this.$('.accordion-group[data-module=' + view.module + ']').find('.found-duplicate');
        $foundDuplicatePlaceholder.text(view.collection.length + ' duplicates found'); //todo translate
    }
})