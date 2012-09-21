({
    extendsFrom:'AlertView',
    initialize: function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.on('quickcreate:alert:show', this.showAlert, this);
        this.context.on('quickcreate:alert:dismiss', this.dismissAlert, this);
    },
    showAlert: function(message){
        debugger;
       this.dismissAlert();
       var options = {
            level: 'warning',
            messages: message,
            autoClose: false};

        this.alert = this.show(options);

    },
    dismissAlert: function() {
        if(this.alert) {
            this.alert.close();
        }
    }
})