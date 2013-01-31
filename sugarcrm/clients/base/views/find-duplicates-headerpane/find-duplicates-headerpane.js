({
    extendsFrom: 'HeaderpaneView',

    events: {
        'click a[name=cancel_button]': 'cancel',
        'click a[name=merge_duplicates_button]': 'mergeDuplicates'
    },

    /**
     * Set the title
     */
    _renderHtml: function() {
        this.title = app.lang.get("LBL_DUP_MERGE");
        app.view.views.HeaderpaneView.prototype._renderHtml.call(this);
    },

    /**
     * Wait for the mass_collection to be set up so we can add listener
     */
    bindDataChange: function() {
        app.view.views.HeaderpaneView.prototype.bindDataChange.call(this);
        this.context.on('change:mass_collection', this.addMassCollectionListener, this);
    },

    /**
     * Set up add/remove listener on the mass_collection so we can enable/disable the merge button
     */
    addMassCollectionListener: function() {
        var massCollection = this.context.get('mass_collection');
        massCollection.on('add', this.toggleMergeButton, this);
        massCollection.on('remove', this.toggleMergeButton, this);
    },

    /**
     * Enable the merge button when a duplicate has been checked
     * Disable when all are unchecked
     */
    toggleMergeButton: function() {
        var disabled;
        if (this.context.get('mass_collection').length > 0) {
            disabled = false;
        } else {
            disabled = true;
        }
        this.$("[name='merge_duplicates_button']").toggleClass('disabled', disabled);
    },

    /**
     * Cancel and close the drawer
     */
    cancel: function() {
        this.context.trigger("drawer:hide");
        if (this.context.parent)
            this.context.parent.trigger("drawer:hide");
    },

    mergeDuplicates: function() {
        var mergeDupesComponent = {
            components: [{
                layout : 'merge-duplicates',
                context: {
                    selectedDuplicates: this.context.get('mass_collection')
                }
            }]
        };
        this.context.trigger("drawer:replace", mergeDupesComponent, this);
        if (this.context.parent) {
            this.context.parent.trigger("drawer:replace", mergeDupesComponent, this);
        }
    }

})
