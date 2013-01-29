({

    events: {
        'click .tour': 'showTourModal'
    },
    tagName: "span",
    _renderHtml: function(){
        this.isAuthenticated = app.api.isAuthenticated();
        app.view.View.prototype._renderHtml.call(this);
    },
    showTourModal: function() {
        // TODO: When the <footer> tags have been replaced/start respecting z-indexes
        // call this.layout.trigger(eventName, ...) to show a modal layout instead
        $('.system-tour').modal('show');
    }
})