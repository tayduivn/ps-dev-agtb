({

    events: {
        'click .tour': 'systemTourModal',
        'click .tour-module-start': 'startSystemTour',
        'click .tour-full-start': 'startSystemTour'
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
    systemTourModal: function() {
        // check to make sure you're not already touring the system
        if(app.view.views.TourView) {
            if(app.view.views.TourView.prototype.tourMode !== true){
                $('.system-tour').modal('show');
            }
        } else {  // Show default tour modal
            $('.system-tour').modal('show');
        }
    },
    startSystemTour: function(e) {
        // If "Full Tour" was clicked, relay this to startTour(),
        // to determine whether or not to route to the homepage
        var fullTour = this.$(e.target).hasClass("tour-full-start") ? true: false,
            currentModule = app.controller.layout.options.module,
            viewType = app.controller.layout.options.name;

        $('.system-tour').modal('hide');
        app.view.views.TourView.prototype.startTour(currentModule, viewType, fullTour);
    }

})