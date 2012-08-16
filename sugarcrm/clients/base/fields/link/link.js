({
    events: {
        'keyup .search-field > input': 'throttleSearch'
    },
    /**
     * Initializes field and binds all function calls to this
     * @param {Object} options
     */
    initialize: function(options) {
        _.bindAll(this);
        
        var self = this;
        self.myView = options.view.name;
        this.options = options;
        this.listItems = [];

        this.currCollection = this.app.data.createRelatedCollection(this.model,options.def.name);
        this.currCollection.bind("change",function() {self.updateOptions(self); });
        this.currCollection.bind("reset",function() {self.updateOptions(self); });

        this.searchCollection = this.app.data.createRelatedCollection(this.model,options.def.name);

        this.app.view.Field.prototype.initialize.call(this, options);

    },

    /**
     * Updates the "selected" options in the option dropdown
     */
    updateOptions: function(self) {
        var myEl;
        if ( self.myView == 'edit' ) {
            self.myListTemplate = self.app.template.getField(self.type,'editlist');
            myEl = self.$('select');
        } else {
            self.myListTemplate = self.app.template.getField(self.type,'detaillist');
            myEl = self.$el;
        }
        var currItems = self.currCollection.models;
        var addItems = self.searchCollection.models;

        self.listItems = [];
        // Add all of the pre-existing selections
        for ( var i = 0; i < currItems.length; i++ ) {
            self.listItems[self.listItems.length] = { 'module': self.currCollection.module, 'name': currItems[i].get('name'), 'id': currItems[i].id, 'selected': 'selected="selected"' };
            // mySelect.append($(new Option(currItems[i].get('name'),currItems[i].id,'selected')).attr('selected',true));
        }
        for ( var i = 0; i < addItems.length; i++ ) {
            // Don't double-add anything that is already in the list
            if ( typeof self.currCollection.get(addItems[i].id) == 'undefined' ) {
                self.listItems[self.listItems.length] = { 'module': self.currCollection.module, 'name': addItems[i].get('name'), 'id': addItems[i].id, 'selected': '' };
                // mySelect.append(new Option(addItems[i].get('name'),addItems[i].id));
            }
        }

        // Reset the html for this
        myEl.html(self.myListTemplate(self));
        if ( self.myView == 'edit' ) {
            myEl.trigger("liszt:updated");
        }
    },

    /**
     * Renders relate field
     */
    _render: function() {
        var self = this;
        var result = this.app.view.Field.prototype._render.call(this);
        if ( self.myView == 'edit' ) {
            this.$(".relateEdit").chosen({
                no_results_text: "Searching for " // TODO Add labels support
            }).change(function(event) {

                var urlBase = self.app.api.buildURL(self.module+'/'+self.collection.models[0].id,'link/'+self.name+'/');
                
                var selectedItems = $(event.target).find(':selected');
                var idList = {};
                for ( var i = 0 ; i < self.currCollection.models.length ; i++ ) {
                    idList[self.currCollection.models[i].id] = false;
                }
                for ( var i = 0 ; i < selectedItems.length ; i++ ) {
                    if ( typeof idList[selectedItems[i].value] == 'undefined' ) {
                        // It's not in the current collection, it must be new.
                        self.currCollection.add(self.searchCollection.get(selectedItems[i].value));
                        
                        self.app.api.call('update',urlBase+selectedItems[i].value,{});
                    } else {
                        // Let's mark this ID as still being selected
                        idList[selectedItems[i].value] = true;
                    }
                }
                
                for ( var i in idList ) {
                    if ( !idList[i] ) {
                        // This id was never looped over, so it is no longer selected
                        // Remove it from the collection
                        self.currCollection.remove(self.currCollection.get(i));
                        
                        self.app.api.call('delete',urlBase+i);
                    }
                }
                
                self.updateOptions(self);
                
                
            });
        }
        self.currCollection.fetch({relate:true});
        return result;
    },
    /**
     * Throttles search ajax
     * @param {Object} e event object
     * @param {Integer} interval interval to throttle
     */
    throttleSearch: function(e, interval) {
        if (interval === 0 && e.target.value != "") {
            this.search(e);
            return;
        } else {
            interval = 500;
            clearTimeout(this.throttling);
            delete this.throttling;
        }

        this.throttling = setTimeout(this.throttleSearch, interval, e, 0);
    },
    /**
     * Searches for related field
     * @param event
     */
    search: function(event) {
        fizzSearchEvent = event;

        var self = this;
        self.searchCollection.fetch({
            params: {q:event.target.value},  // TODO update this to filtering API
            success: function(data) {
                if (data.models.length > 0) {
                    self.updateOptions(self);
                } else {
                    //TODO trigger error we found nothing
                }
            }

        });
    }

})