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
        this.currentModule = module;
        this.viewType = viewType;
        this.fullTour = fullTour;

        //console.log("startTour::fullTour", fullTour);
        //console.log("startTour::this.fullTour", this.fullTour);
        // Route to the home page if the user clicked "Full Tour"
        if( fullTour ) {
            //TODO: When we have a "Home" module for summer, change this
            module = "ActivityStream";
            viewType = "dashboard";
            app.router.navigate("#", {trigger: true});
        }

        this.populatePopovers(module, viewType);
    },
    endTour: function(context, obj) {
        context.filter("[data-tour='" + obj.id + "']").popover("hide");
        this.tourMode = false;
        app.alert.show("tour_end", {level: "info", title:"End of Tour",
            messages: "Thank you for taking the Summer tour! You can re-take the tour anytime by clicking the 'Tour' button in the footer.", autoClose: true});
        return;
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
        var self = this;

        // hide the current popover
        // show the next popover
        // bind next/prev functions

        if( obj === _.last(currentArray) ) {
            // Conditions to end the tour
            if( (this.fullTour && this.currentModule === "Opportunities" && this.viewType === "record") ||
                !(this.fullTour) && (this.viewType === "record" || this.viewType === "dashboard" ) ) {
                this.endTour(context, obj);
                return;
            }
            // figure out routing
            // call populatePopovers()

            // If you're here, fulltour == true and viewtype == anything except record-opps OR
            // fulltour == false, and viewtype == list
            switch(this.currentModule, this.viewType) {
                case "ActivityStream", "dashboard":
                    app.router.navigate("#Accounts", {trigger: true});
                    //console.log("tourmode", this.tourMode);
                    this.populatePopovers("Accounts", "list");
                    break;
                case "Accounts", "list":
                    // app.router.navigate("#Accounts/create", {trigger: true});
                    // this.populatePopovers
                    break;
                case "Accounts", "newrecord":
                    break;
                case "Accounts", "record":
                    break;
                case "Contacts", "list":
                    break;
                case "Contacts", "record":
                    break;
                case "Opportunities", "list":
                    break;
             }
            this.currentModule = app.controller.layout.options.module;
            this.viewType = app.controller.layout.options.name;
            this.populatePopovers(this.currentModule, this.viewType);
        }
        else {
            var nextIndex = index + 1,
                nextObj = currentArray[nextIndex];

            context.filter("[data-tour='" + obj.id + "']").popover("hide");
            context.filter("[data-tour='" + nextObj.id + "']").popover("show");

            this.fixPopoverPosition(nextObj.placement, nextObj.id);

            // bind event listeners
            $(".tour-next").on("click", function() {
                self.nextItem(context, nextIndex, nextObj, currentArray, data);
            });
            $(".tour-prev").on("click", function() {
                self.prevItem(context, nextIndex, nextObj, currentArray, data);
            });
            $(".tour-end").on("click", function() {
                self.endTour(context, nextObj);
            });
        }
    },
    prevItem: function(context, index, obj, currentArray, data) {
        var self = this;

        //console.log("prev");

        context.filter("[data-tour='" + obj.id + "']").popover("hide");
        var prevIndex = index - 1,
            prevObj = currentArray[prevIndex];

        context.filter("[data-tour='" + prevObj.id + "']").popover("show");

        // bind event listeners
        $(".tour-next").on("click", function() {
            self.nextItem(context, prevIndex, prevObj, currentArray, data);
        });
        $(".tour-prev").on("click", function() {
            self.prevItem(context, prevIndex, prevObj, currentArray, data);
        });
        $(".tour-end").on("click", function() {
            self.endTour(context, prevObj);
        });
    },
    populatePopovers: function(module, viewType) {
        var self = this,
            $tourEl = $("[data-tour]"),
            tourData = {
                "ActivityStream":{
                    "dashboard": [
                        {"id":"tour-navbar-Accounts", "title":"Accounts", "content":"Text for accounts navbar link", "placement":"bottom"},
                        {"id":"tour-navbar-Contacts", "title":"Contacts", "content":"Text for contacts navbar link", "placement":"bottom"},
                        {"id":"tour-navbar-Opportunities", "title":"Opportunities", "content":"This link will navigate to the Opportunities page, which outlines all deals in each sales stage (new, closed, won, lost, etc).", "placement":"bottom"},
                        {"id":"tour-navbar-search", "title":"Global Search", "content":"Text for global search", "placement":"bottom"},
                        {"id":"tour-navbar-user", "title":"User Menu", "content":"Text for navbar user menu", "placement":"bottom"},
                        {"id":"tour-navbar-quickcreate", "title":"Quick Create Menu", "content":"Text for navbar quick create", "placement":"bottom"},
                        {"id":"tour-stream-filter", "title":"Activity Stream Filters", "content":"Text for activity stream filters", "placement":"right"},
                        {"id":"tour-stream-post", "title":"Activity Stream Post", "content":"Text for activity stream post", "placement":"right"},
                        {"id":"tour-agenda", "title":"Agenda Widget", "content":"Text for agenda", "placement":"left"},
                        //{"id":"tour-recommended-contacts", "title":"Recommended Contacts", "content":"Text for recommended contacts", "placement":"left"},
                        //{"id":"tour-recommended-invites", "title":"Recommended Invites", "content":"Text for recommended invites", "placement":"left"},
                        {"id":"tour-instances", "title":"Your Summer Instances", "content":"Text for instance picker", "placement":"top"},
                        {"id":"tour-todo", "title":"Todo List Widget", "content":"Text for todo widget", "placement":"top"}
                    ]
                },
                "Accounts":{
                    "list": [
                        {"id":"tour-Accounts-list-view", "title":"List of Companies", "content":"Text for accounts list view", "placement":"right"},
                        {"id":"tour-Accounts-list-create", "title":"Create a Company", "content":"Text for accounts list create button", "placement":"bottom"},
                        {"id":"tour-Accounts-list-search", "title":"Filter/Search Companies", "content":"Text for accounts list search/filter", "placement":"bottom"},
                        {"id":"tour-countrychart", "title":"Sales Country Chart", "content":"Text for accounts country chart", "placement":"left"},
                        {"id":"tour-Accounts-stream", "title":"Accounts", "content":"Text for accounts activity stream", "placement":"left"}
                    ],
                    "newrecord": [
                        {"id":"tour-accounts-record-businesscard", "content":"Text for accounts record business card", "placement":"top"},
                        {"id":"tour-accounts-record-stream", "content":"Text for accounts record activity stream", "placement":"top"},
                        {"id":"tour-accounts-record-todos", "content":"Text for accounts record related tasks", "placement":"top"},
                        {"id":"tour-accounts-record-map", "content":"Text for company map", "placement":"top"}
                    ],
                    "record": [
                        {"id":"tour-accounts-record-businesscard", "content":"Text for accounts record business card", "placement":"top"},
                        {"id":"tour-accounts-record-stream", "content":"Text for accounts record activity stream", "placement":"top"},
                        {"id":"tour-accounts-record-todos", "content":"Text for accounts record related tasks", "placement":"top"},
                        {"id":"tour-accounts-record-map", "content":"Text for company map", "placement":"top"}
                    ]
                },
                "Contacts": {
                    "list": [
                        {"id":"tour-accounts-list-view", "content":"Text for accounts list view", "placement":"right"},
                        {"id":"tour-accounts-countrychart", "content":"Text for accounts country chart", "placement":"top"},
                        {"id":"tour-accounts-stream", "content":"Text for accounts activity stream", "placement":"top"},
                        {"id":"tour-accounts-list-search", "content":"Text for accounts list search/filter", "placement":"top"},
                        {"id":"tour-accounts-list-create", "content":"Text for accounts list create button", "placement":"top"}
                    ],
                    "record": [
                        {"id":"tour-accounts-record-businesscard", "content":"Text for accounts record business card", "placement":"top"},
                        {"id":"tour-accounts-record-stream", "content":"Text for accounts record activity stream", "placement":"top"},
                        {"id":"tour-accounts-record-todos", "content":"Text for accounts record related tasks", "placement":"top"},
                        {"id":"tour-accounts-record-map", "content":"Text for company map", "placement":"top"}
                    ]
                },
                "Opportunities":{
                    "list": [
                        {"id":"tour-accounts-list-view", "content":"Text for accounts list view", "placement":"right"},
                        {"id":"tour-accounts-countrychart", "content":"Text for accounts country chart", "placement":"top"},
                        {"id":"tour-accounts-stream", "content":"Text for accounts activity stream", "placement":"top"},
                        {"id":"tour-accounts-list-search", "content":"Text for accounts list search/filter", "placement":"top"},
                        {"id":"tour-accounts-list-create", "content":"Text for accounts list create button", "placement":"top"}
                    ],
                    "record": [
                        {"id":"tour-accounts-record-businesscard", "content":"Text for accounts record business card", "placement":"top"},
                        {"id":"tour-accounts-record-stream", "content":"Text for accounts record activity stream", "placement":"top"},
                        {"id":"tour-accounts-record-todos", "content":"Text for accounts record related tasks", "placement":"top"},
                        {"id":"tour-accounts-record-map", "content":"Text for company map", "placement":"top"}
                    ]
                }
            };

        _.each(tourData[module][viewType], function(value, key, list) {
            //console.log("value: ", value);
            //console.log("key: ", key);
            //console.log("list: ", list);

            var nextPrevEl = '<div class="popover '+ value.id + '"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div><div class="modal-footer" style="position: relative;"><a class="btn tour-end">End Tour</a><a class="btn btn-primary tour-prev">Prev</a><a class="btn btn-primary tour-next">Next</a></div></div></div>',
                currentEl = $tourEl.filter("[data-tour='" + value.id + "']");

            // If this is the first popover item, show it, bind button listeners
            if( value === _.first(list) ) {
                var nextEl = '<div class="popover '+ value.id + '"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div><div class="modal-footer" style="position: relative;"><a class="btn tour-end">End Tour</a><a class="btn btn-primary tour-next">Next</a></div></div></div>';
                currentEl.popover({title: value.title, content: value.content, placement: value.placement, trigger: "manual", template: nextEl});
                $tourEl.filter("[data-tour='" + value.id + "']").popover("show");
                self.fixPopoverPosition(value.placement, value.id);

                $(".tour-next").on("click", function() {
                    self.nextItem($tourEl, key, value, list, tourData);
                });
                $(".tour-end").on("click", function() {
                    self.endTour($tourEl, value);
                });
            }
            else {
                currentEl.popover({title: value.title, content: value.content, placement: value.placement, trigger: "manual", template: nextPrevEl});
            }
        });
    },
    fixPopoverPosition: function(currentPlacement, className) {
        var windowEl = $(window),
            popoverEl = $("." + className).children(".popover-inner"),
            viewportWidth = windowEl.width(),
            viewportHeight = windowEl.height(),
            popoverWidth = popoverEl.width(),
            popoverHeight = popoverEl.height(),
            xyOffset = popoverEl.offset(),
            buffer = 6;

        switch(currentPlacement) {
            // shift left or right
            case "top":
            case "bottom":
                var rightPos = xyOffset.left + popoverWidth

                if( xyOffset.left < 0 ) {
                    var leftOffset = (xyOffset.left)*(-1) + buffer;
                    popoverEl.css("position", "relative");
                    popoverEl.css("left", leftOffset);
                }
                else if( rightPos > viewportWidth ) {
                    var rightOffset = (rightPos - viewportWidth) + buffer;
                    popoverEl.css("position", "relative");
                    popoverEl.css("right", rightOffset);
                }
                break;
            // shift up or down
            case "left":
            case "right":
                var bottomPos = xyOffset.top + popoverHeight;

                if( xyOffset.top < 0 ) {
                    var topOffset = (xyOffset.top)*(-1) + buffer;
                    popoverEl.css("position", "relative");
                    popoverEl.css("top", topOffset);
                }
                else if( bottomPos > viewportHeight ) {
                    var bottomOffset = (bottomPos - viewportHeight) + buffer;
                    popoverEl.css("position", "relative");
                    popoverEl.css("bottom", bottomOffset);
                }
                break;
        }
        // Currently the z-index for popover is 1010, which hides it under
        // the navbar and the footer. Boost it to 1030 to account for that.
        $("." + className).css("z-index", 1030);
    }
})
