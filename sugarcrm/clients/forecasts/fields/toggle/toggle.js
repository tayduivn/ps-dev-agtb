({
    /**
     * Attach a click event to <span class="edit"> field.  See edit.hbt file
     */
    events : { 'click .iphone-toggle-buttons span.edit' : 'toggle' },

    /**
     * Function to handle the click event.  In this case we just trigger the selectedToggle context event
     *
     * @param event
     */
    toggle : function(event) {
       this.view.context.set('selectedToggle', this);
    }

})
