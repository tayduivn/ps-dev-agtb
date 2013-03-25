({
    events: {
        'click #tour': 'showTutorial',
        'click #print': 'print',
        'click #top': 'top'
    },
    tagName: "span",
    handleViewChange: function() {
        if (app.tutorial.hasTutorial()) {
            this.enableTourButton();
        } else {
            this.disableTourButton()
        }
    },
    enableTourButton: function() {
        this.$('#tour').removeClass('disabled');
        this.events['click #tour'] = 'showTutorial';
        this.undelegateEvents();
        this.delegateEvents();
    },
    disableTourButton: function() {
        this.$('#tour').addClass('disabled');
        delete this.events['click #tour'];
        this.undelegateEvents();
        this.delegateEvents();
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        app.events.on("app:view:change", this.handleViewChange, this);
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
    },
    showTutorial: function() {
        app.tutorial.resetPrefs();
        app.tutorial.show(app.controller.context.get('layout'),{module:app.controller.context.get('module')});
    }
})

