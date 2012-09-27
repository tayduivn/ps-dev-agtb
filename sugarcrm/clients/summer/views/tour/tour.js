({
    events: {
    },
    initialize: function(options) {
        var self = this;
        app.view.View.prototype.initialize.call(this, options);
    },
    startTour: function(module, viewType, fullTour) {
        this.tourMode = true;
        this.currentModule = module;
        this.viewType = viewType;
        this.fullTour = fullTour;

        // Route to the home page if the user clicked "Full Tour"
        if( fullTour ) {
            //TODO: When we have a "Home" module for summer, change this
            this.currentModule = "ActivityStream";
            this.viewType = "dashboard";
            app.router.navigate("#", {trigger: true});
        }

        this.initPopover(this.currentModule, this.viewType);
    },
    endTour: function() {
        this.tourMode = false;
        app.alert.show("tour_end", {level: "info", title:"End of Tour",
            messages: "Thank you for taking the Summer tour! You can re-take the tour anytime by clicking the 'Tour' button in the footer.", autoClose: true});
        return;
    },
    nextItem: function(index, obj, currentArray, data) {
        //console.log("next");
        var self = this;

        if( obj === _.last(currentArray) ) {
            // Conditions to end the tour
            if( !(this.fullTour) || (this.fullTour && this.currentModule === "Opportunities" && this.viewType === "record") ) {
                $("[data-tour='" + obj.id + "']").popover("hide");
                this.endTour();
                return;
            }

            // If you're here, fulltour == true and viewtype == anything except record-opps OR
            // fulltour == false, and viewtype == list
            switch(this.currentModule, this.viewType) {
                case "ActivityStream", "dashboard":
                    app.router.navigate("#Accounts", {trigger: true});
                    break;
                case "Accounts", "list":
                    app.router.navigate("#Accounts/create", {trigger: true});
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

            // get the new module and viewtype
            this.currentModule = app.controller.layout.options.module;
            this.viewType = app.controller.layout.options.name;
            this.initPopover(this.currentModule, this.viewType);
        }
        else {
            var nextIndex = index + 1,
                nextObj = currentArray[nextIndex],
                $nextEl = $("[data-tour='" + nextObj.id + "']"),
                templateEl = '<div class="popover '+ nextObj.id + '"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div><div class="modal-footer" style="position: relative;"><a class="btn tour-end">End Tour</a><a class="btn btn-primary tour-prev">Prev</a><a class="btn btn-primary tour-next">Next</a></div></div></div>';

            // hide the current popover
            $("[data-tour='" + obj.id + "']").popover("hide");

            // show the next popover
            if( $nextEl.length > 0 ) {
                // If its not a full tour, don't instruct the user to take certain actions, this is done by
                // overriding the content with custom "not full tour" content.
                var popoverContent = !(this.fullTour) ? (nextObj["content_not_full"] || nextObj.content) : nextObj.content;

                $nextEl.popover({title: nextObj.title, content: popoverContent, placement: nextObj.placement,
                                 trigger: "manual", template: templateEl}).popover("show");

                this.fixPopoverPosition(nextObj.placement, nextObj.id);
                this.bindClickEvents(self, $nextEl, nextIndex, nextObj, currentArray, data);
            }
        }
    },
    prevItem: function(index, obj, currentArray, data) {
        var self = this;
        //console.log("prev");

        var prevIndex = index - 1,
            prevObj = currentArray[prevIndex],
            $prevEl = $("[data-tour='" + prevObj.id + "']");

        $("[data-tour='" + obj.id + "']").popover("hide");
        $prevEl.popover("show");

        this.bindClickEvents(self, $prevEl, prevIndex, prevObj, currentArray, data);
    },
    initPopover: function(module, viewType) {
        var self = this;

        $.getJSON("../clients/summer/views/tour/data.json", null, function(tourData) {
            if( tourData.error ) {
                app.alert.show('retrieve_failed', {level: 'error', title:'Tour Failed', messages: 'Failed to retrieve tour data: '+ tourData.error, autoClose: false});
                return;
            }
            else {
                var list = tourData[module][viewType],
                    firstObj = _.first(list),
                    $currentEl = $("[data-tour='" + firstObj.id + "']");

                if( $currentEl.length > 0 ) {
                    var templateEl = '<div class="popover '+ firstObj.id + '"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div><div class="modal-footer" style="position: relative;"><a class="btn tour-end">End Tour</a><a class="btn btn-primary tour-next">Next</a></div></div></div>';

                    $currentEl.popover({title: firstObj.title, content: firstObj.content, placement: firstObj.placement,
                        trigger: "manual", template: templateEl}).popover("show");

                    self.fixPopoverPosition(firstObj.placement, firstObj.id);
                    self.bindClickEvents(self, $currentEl, 0, firstObj, list, tourData);
                    // return
                }
                else
                {
                    console.log("oops!");
                    // try next object in list
                    // if it works, return
                }
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
    },
    bindClickEvents: function(context, $el, index, obj, list, data) {

        $(".tour-next").on("click", function() {
            context.nextItem(index, obj, list, data);
        });
        $(".tour-end").on("click", function() {
            $el.popover("hide");
            context.endTour();
        });

        if( $(".tour-prev").length > 0 )
        {
            $(".tour-prev").on("click", function() {
                context.prevItem(index, obj, list, data);
            });
        }
    }
})
