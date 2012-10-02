({
    extendsFrom:'AlertView',
    events: {
        'click .alert-action-save' : 'save'
    },

    initialize: function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.on('quickcreate:alert:show:dupfound', this.showDupFoundAlert, this);
        this.context.on('quickcreate:alert:show:recordcreated', this.showRecordCreatedAlert, this);
        this.context.on('quickcreate:alert:show:servererror', this.showServerErrorAlert, this);
        this.context.on('quickcreate:alert:dismiss', this.dismissAlert, this);
    },

    showDupFoundAlert: function(dupCount){
        this.dismissAlert();
        var options = {
            level: 'warning',
            messages: this.getDupFoundAlertMessage(dupCount),
            autoClose: false
        };

        this.alert = this.show(options);
    },

    showRecordCreatedAlert: function(useGlobalAlert){
        var options = {
            level: 'info',
            messages: this.getRecordCreatedAlertMessage(),
            autoClose: true,
            autoCloseAfter: 5000
        };

        if (useGlobalAlert) {
            app.alert.show('quickcreate:recordcreated', options);
        } else {
            this.dismissAlert();
            this.alert = this.show(options);
        }
    },

    showServerErrorAlert: function(){
        this.dismissAlert();
        var options = {
            level: 'error',
            messages: this.getServerErrorAlertMessage(),
            autoClose: false
        };

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
    getDupFoundAlertMessage: function(dupCount) {
        return "<span class=\"alert-message\">" +
            "<strong>" + dupCount + " Duplicate Records.</strong>  You can " +
            "<a class='alert-action-save'>ignore duplicates and save</a> or select to edit one of the duplicates." +
            "</span>";
    },

    getRecordCreatedAlertMessage: function() {
        return "<span class=\"alert-message\">" +
            "<strong>Record successfully created.</strong>" +
            "</span>";
    },

    getServerErrorAlertMessage: function() {
        return "<span class=\"alert-message\">" +
            "<strong>Error occurred while connecting to the server. Please try again.</strong>" +
            "</span>";
    }
})