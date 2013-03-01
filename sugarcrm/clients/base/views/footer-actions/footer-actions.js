({
    events: {
        'click .tour': 'showTourModal',
        'click #print': 'print',
        'click #top': 'top'
    },
    tagName: "span",
    _renderHtml: function(){
        this.isAuthenticated = app.api.isAuthenticated();
        app.view.View.prototype._renderHtml.call(this);
    },
    print: function() {
        window.print();
    },
    top: function() {
        scroll(0,0);
    },
    showTourModal: function() {
        // TODO: When the <footer> tags have been replaced/start respecting z-indexes
        // call this.layout.trigger(eventName, ...) to show a modal layout instead
        $('.system-tour').modal('show');
    }
})