({
    events: {
        'click #tour': 'showTutorial',
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
    showTutorial: function() {
        app.tutorial.resetPrefs();
        app.tutorial.show(app.controller.context.get('layout'),{module:app.controller.context.get('module')});
    }
})

