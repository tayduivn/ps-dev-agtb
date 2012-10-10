({
    events: {
        'click .interactions-link': 'contentSwitcher'
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);
        this.collections = {
            "calls": {
                "count": 0,
                "data": []
            },
            "meetings": {
                "count": 0,
                "data": []
            },
            "emails": {
                "count": 0,
                "data": []
            }
        };
        this.callsActive = false;
        this.emailsActive = false;
        this.meetingsActive = false;
    },
    loadData: function() {
        var self = this;

        app.api.call("read", app.api.buildURL(app.controller.layout.options.module + "/" + app.controller.context.get("model").id + "/" +"interactions"), null,
        { success: function(data) {
            self.collections = data;
            self.callsActive = true;
            self.emailsActive = false;
            self.meetingsActive = false;
            self.render();
          }
        });
    },
    contentSwitcher: function(e) {
        var $target = this.$(e.target);
        if( !($target.parents(".interactions-link").is(".active")) ) {
            // remove active class from the active 'a' element
            this.$(".interactions-link.active").removeClass("active");

            // add the active class to the clicked 'a' element
            $target.parents(".interactions-link").addClass("active");

            switch($target.parents("article").attr("class")) {
                case "interactions-calls":
                    this.callsActive = true;
                    this.emailsActive = false;
                    this.meetingsActive = false;
                    break;
                case "interactions-emails":
                    this.callsActive = false;
                    this.emailsActive = true;
                    this.meetingsActive = false;
                    break;
                case "interactions-meetings":
                    this.callsActive = false;
                    this.emailsActive = false;
                    this.meetingsActive = true;
                    break;
            }

            // will probably change this method to show/hide elements
            // instead of rendering
            this.render();
        }
    }
})