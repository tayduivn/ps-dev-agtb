({
    events: {
    },
    initialize: function(options) {
        var self = this;
        //console.log(options);
        app.view.View.prototype.initialize.call(this, options);
        //console.log("tour::initialize");
    },
    startTour: function(module, viewType, fullTour) {
        //console.log("tour::start");
        this.tourMode = true;

        // Route to the home page if the user clicked "Full Tour"
        if( fullTour ) {
            //TODO: When we have a "Home" module for summer, change this
            module = "ActivityStream";
            viewType = "dashboard";
            app.router.navigate("#", {trigger: true});
        }

        this.populatePopovers(module, viewType);
    },
    endTour: function() {

    },
    nextItem: function(context, index, obj, currentArray, data) {
        /*
        * This function should handle the following:
        * - hiding the current popover element
        * - showing the next popover element
        * - if a next popover element is not defined, you are either:
        *   a) at the end of the tour
        *       - in this case, route to the home page or where
        *         the user was when they started the tour
        *   b) at the end of the tour for a module's view
        *       - in this case, either:
        *           a) route to the module's next view
        *           b) route to the next module
        * */

        //console.log("next");
        var self = this,
            accountsListRoute = app.router.buildRoute("Accounts"),
            contactsListRoute = app.router.buildRoute("Contacts"),
            opportunitiesListRoute = app.router.buildRoute("Opportunities");

        // hide the current popover
        // show the next popover
        // bind next/prev functions
        context.filter("[tour='" + obj.id + "']").popover("hide");

        if( obj == _.last(currentArray) ) {
            // figure out routing
            // call populatePopovers()

            // might have to use _.filter here
        }
        else {
            var nextIndex = index + 1,
                nextObj = currentArray[nextIndex];

            context.filter("[tour='" + nextObj.id + "']").popover("show");

            // bind event listeners
            $(".tour-next").on("click", function() {
                self.nextItem(context, nextIndex, nextObj, currentArray, data);
            });
            $(".tour-prev").on("click", function() {
                self.prevItem(context, nextIndex, nextObj, currentArray, data);
            });
        }
    },
    prevItem: function(context, index, obj, currentArray, data) {
        var self = this,
            accountsListRoute = app.router.buildRoute("Accounts"),
            contactsListRoute = app.router.buildRoute("Contacts"),
            opportunitiesListRoute = app.router.buildRoute("Opportunities");

        //console.log("prev");

        // hide the current popover
        // show the next popover
        // bind next/prev functions
        context.filter("[tour='" + obj.id + "']").popover("hide");

        if( obj == _.first(currentArray) ) {
            // figure out routing
            // call populatePopovers()
        }
        else {
            var prevIndex = index - 1,
                prevObj = currentArray[prevIndex];

            context.filter("[tour='" + prevObj.id + "']").popover("show");

            // bind event listeners
            $(".tour-next").on("click", function() {
                self.nextItem(context, prevIndex, prevObj, currentArray, data);
            });
            $(".tour-prev").on("click", function() {
                self.prevItem(context, prevIndex, prevObj, currentArray, data);
            });
        }
    },
    populatePopovers: function(module, viewType) {
        var self = this,
            nextPrevEl = "<div class='btn-toolbar'><div class='btn-group'><a class='btn btn-primary tour-prev'>Prev</a></div><div class='btn-group'><a class='btn btn-primary tour-next'>Next</a></div></div>",
            $tourEl = $("[tour]"),
            tourData = {
            "ActivityStream":{
                "dashboard": [
                    {"id":"tour-navbar-Accounts", "title": "Accounts", "content":"Text for accounts navbar link", "placement":"bottom"},
                    {"id":"tour-navbar-Contacts", "title": "Contacts", "content":"Text for contacts navbar link", "placement":"bottom"},
                    {"id":"tour-navbar-Opportunities", "title": "Opportunities", "content":"This link will navigate to the Opportunities page, which outlines all deals in each sales stage (new, closed, won, lost, etc).", "placement":"bottom"},
                    {"id":"tour-navbar-search", "title": "Global Search", "content":"Text for global search", "placement":"bottom"},
                    {"id":"tour-navbar-user", "title": "User Menu", "content":"Text for navbar user menu", "placement":"bottom"},
                    {"id":"tour-navbar-quickcreate", "title": "Quick Create Menu", "content":"Text for navbar quick create", "placement":"bottom"},
                    {"id":"tour-stream-filter", "title": "Activity Stream Filters", "content":"Text for activity stream filters", "placement":"right"},
                    {"id":"tour-stream-post", "title": "Activity Stream Post", "content":"Text for activity stream post", "placement":"right"},
                    {"id":"tour-agenda", "title": "Agenda Widget", "content":"Text for agenda", "placement":"left"},
                    {"id":"tour-recommended-contacts", "title": "Recommended Contacts", "content":"Text for recommended contacts", "placement":"left"},
                    {"id":"tour-recommended-invites", "title": "Recommended Invites", "content":"Text for recommended invites", "placement":"left"},
                    {"id":"tour-instances", "title": "Your Summer Instances", "content":"Text for instance picker", "placement":"top"},
                    {"id":"tour-todo", "title": "Todo List Widget", "content":"Text for todo widget", "placement":"top"}
                ]
            }
        };

        _.each(tourData[module][viewType], function(value, key, list) {
            /*console.log("value: ", value);
            console.log("key: ", key);
            console.log("list: ", list);*/

            var currentEl = $tourEl.filter("[tour='" + value.id + "']");
            currentEl.popover({title: value.title, content: "<p>" + value.content + "</p>" + nextPrevEl, placement: value.placement, trigger: "manual"});

            // If this is the first popover item, show it, bind next/prev listeners
            if( key == 0 ) {
                $tourEl.filter("[tour='" + value.id + "']").popover("show");

                $(".tour-next").on("click", function() {
                    self.nextItem($tourEl, key, value, list, tourData);
                });
                $(".tour-prev").on("click", function() {
                    self.prevItem($tourEl, key, value, list, tourData);
                });
            }
        });
    }
})
