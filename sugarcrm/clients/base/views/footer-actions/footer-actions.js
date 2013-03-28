({
    events: {
        'click #tour': 'showTutorial',
        'click #feedback': 'feedback',
        'click #support': 'support'
    },
    tagName: "span",
    _renderHtml: function(){
        this.isAuthenticated = app.api.isAuthenticated();
        app.view.View.prototype._renderHtml.call(this);
    },
    feedback: function() {
        window.open("http://www.surveymonkey.com/s/FNCXF3S","_blank");
    },
    support: function() {
        window.open("http://support.sugarcrm.com", '_blank');;
    },
    showTutorial: function() {
        app.tutorial.resetPrefs();
        app.tutorial.show(app.controller.context.get('layout'),{module:app.controller.context.get('module')});
    }
})

