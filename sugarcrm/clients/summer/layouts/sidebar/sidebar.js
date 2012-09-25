({
    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);

        // TODO: Fix this, right now app.template.getLayout does not retrieve the proper template because
        // it builds the wrong name.
        this.template = app.template.get("l.sidebar");
        main = this;

        console.log("Making a sidebar layout");
    }
})