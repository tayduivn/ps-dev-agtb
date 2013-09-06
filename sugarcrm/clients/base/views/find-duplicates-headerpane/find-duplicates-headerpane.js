({
    extendsFrom: 'HeaderpaneView',

    events: {
        'click a[name=cancel_button]': 'cancel',
        'click a[name=merge_duplicates_button]:not(".disabled")': 'mergeDuplicatesClicked'
    },

    plugins: ['Merge'],

    /**
     * Wait for the mass_collection to be set up so we can add listener
     */
    bindDataChange: function() {
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: 'bindDataChange'});
        this.on('mergeduplicates:complete', this.mergeComplete, this);
        this.context.on('change:mass_collection', this.addMassCollectionListener, this);
    },

    /**
     * {@inheritDoc}
     * Dispose safe for mass_collection
     */
    unbindData: function() {
        var massCollection = this.context.get('mass_collection');

        if (massCollection) {
            massCollection.off(null, null, this);
        }
        app.view.View.prototype.unbindData.call(this);
    },

    /**
     * Set up add/remove listener on the mass_collection so we can enable/disable the merge button
     */
    addMassCollectionListener: function() {
        var massCollection = this.context.get('mass_collection');
        massCollection.on('add remove', this.toggleMergeButton, this);
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
        app.drawer.close();
    },

    /**
     * Close the current drawer window by passing merged primary record
     * once merge process is complete.
     *
     * @param {Backbone.Model} primaryRecord Primary Record.
     */
    mergeComplete: function(primaryRecord) {
        app.drawer.closeImmediately(true, primaryRecord);
    },

    /**
     * Merge records handler.
     */
    mergeDuplicatesClicked: function() {
        this.mergeDuplicates(this.context.get('mass_collection'), this.collection.dupeCheckModel);
    }
})
