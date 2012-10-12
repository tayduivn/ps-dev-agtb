({
    events: {
        'click #print': 'print',
        'click #top': 'top'
    },
    tagName: "span",
    initialize: function(options){
        app.events.on("app:sync:complete", this.render, this);
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
    },
    _renderHtml: function(){
        this.isAuthenticated = app.api.isAuthenticated();
        app.view.View.prototype._renderHtml.call(this);
    },
    print: function() {
        window.print();
    },
    top: function() {
        scroll(0,0);
    }
})