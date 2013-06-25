({
    extendsFrom: 'HeaderpaneView',

    events: {
        'click a[name=cancel_button]': 'cancel',
        'click a[name=merge_duplicates_button]:not(".disabled")': 'mergeDuplicates'
    },

    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: 'initialize', args:[options]});
        this.template = app.template.getView('headerpane');
    },

    /**
     * Wait for the mass_collection to be set up so we can add listener
     */
    bindDataChange: function() {
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: 'bindDataChange'});
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
        app.drawer.close();
    },

    mergeDuplicates: function() {
        app.drawer.load({
            layout : 'merge-duplicates',
            context: {
                primaryRecord: this.context.get('dupeCheckModel'),
                selectedDuplicates: this.context.get('mass_collection')
            }
        });
    }

})
