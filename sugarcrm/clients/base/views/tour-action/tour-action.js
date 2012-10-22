({

    events: {
        'click .tour': 'showTourModal'
    },
    tagName: "span",
    initialize: function(options) {
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
    },
    _renderHtml: function(){
        this.isAuthenticated = app.api.isAuthenticated();
        app.view.View.prototype._renderHtml.call(this);
    },
    showTourModal: function() {
        $('.system-tour').modal('show');
    }
})