({
    extendsFrom:'AlertView',
    events: {
        'click .alert-action-save' : 'save'
    },
    initialize: function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.on('quickcreate:alert:show', this.showAlert, this);
        this.context.on('quickcreate:alert:dismiss', this.dismissAlert, this);
    },
    showAlert: function(dupCount){
        this.dismissAlert();
        var options = {
            level: 'warning',
            messages: this.getAlertMessage(dupCount),
            autoClose: false};

        this.alert = this.show(options);

    },
    dismissAlert: function() {
        if(this.alert) {
            this.alert.close();
        }
    },

    save: function() {
        this.context.trigger('quickcreate:save');
    },

    /**
     * Generates the alert message that will be shown for duplicate messages
     * to either true or false.
     * @param {number} dupCount
     * @return {string} The duplicate message as an html string.
     */
    getAlertMessage: function(dupCount) {
        return "<span class=\"alert-message\">" +
            "<strong>" + dupCount + " Duplicate Records.</strong>  You can " +
            "<a class='alert-action-save'>ignore duplicates and save</a> or select to edit one of the duplicates." +
            "</span>";
    }
})