({
    events: {
        'click .tour-type': 'startTour',
        'click .tour-overview': 'startOverview'
    },
    initialize: function(options) {
        var self = this;
        app.view.View.prototype.initialize.call(this, options);
        app.events.on("app:view:change", function(viewType, obj) {
            self.module = obj.module;
            self.viewType = viewType;

            if( self.tourData ) {
                if(!self.tourData[self.module]) {
                    app.logger.debug("Tour data does not contain current module: " + self.module);
                } else if(!self.tourData[self.module][self.viewType]) {
                    app.logger.debug("Tour data does not contain current view: " + self.viewType);
                } else if(!self.tourData[self.module][self.viewType]["tour_overview_data"]) {
                    app.logger.debug("Tour data does not contain an overview for the current view.");
                } else {
                    self.overviewData = self.tourData[self.module][self.viewType]["tour_overview_data"];
                }
                if( self.tourMode ) {
                    self.initPopover(self.module, self.viewType);
                }
            }
        });
        app.events.on("app:sync:complete", function() {
            self.tourBgImg = app.config.siteUrl + "/clients/base/views/tourguide/tour.jpg";
            self.render();
            if( app.user.get("show_tour") ) {
                // disable the tour flag on initial app sync
                app.api.call("update", app.api.buildURL("me/tour"));
                self.$(".system-tour").modal("show");
            }
            // can't call loadData() on an additionalComponent
            // so we call a custom getData() on app:sync:complete
            self.getData();
        });
    },
    startOverview: function() {
        // TODO: Change this when we have modal layout in footer.php
        this.$(".system-tour").modal("hide");
        if(!this.disposed){
            this.render();
            this.$(".system-tour-overview").modal("show");
        }
    },
    startTour: function(e) {
        var data = this.$(e.currentTarget).data();
        this.fullTour = ( data.type === "full" ) ? true : false;

        this.$(".system-tour").modal("hide");

        // If you're already touring the system, remove the current tour popover
        if( this.tourMode ) {
            var $popoverEl = $(".popover[class*='tour']");

            if( $popoverEl.length ) {
                $popoverEl.remove();
            }
        }

        this.tourMode = true;

        // Route to the home page if the user clicked "Full Tour"
        if( this.fullTour ) {
            app.router.navigate("#", {trigger: true});
        }
        this.initPopover(this.module, this.viewType);
    },
    endTour: function() {
        this.tourMode = false;
        app.alert.show("tour_end", {level: "info", title:"End of Tour",
            messages: "Thank you for taking the Summer tour! You can re-take the tour anytime by clicking the 'Tour' " +
                "button in the footer.", autoClose: true});
        return;
    },
    nextItem: function(index, obj, currentArray) {
        var self = this,
            $tourEl = $("[data-tour='" + obj.id + "']");

        if( obj === _.last(currentArray) ) {
            if( $tourEl.length ) {
                $tourEl.popover("hide");
            }

            // Conditions to end the tour
            if( !(this.fullTour) || (this.fullTour && this.module === "Opportunities" && this.viewType === "record") ) {
                this.endTour();
                return;
            }

            // Routing conditions
            // TODO: Change this when we have a "Home" module
            if( this.module === "ActivityStream" ) {
                app.router.navigate("#Accounts", {trigger: true});
            }
            else if( this.module === "Accounts" ) {
                switch( this.viewType ) {
                    case "records":
                        app.router.navigate("#Accounts/create", {trigger: true});
                        break;
                    case "newrecord":
                        app.router.navigate("#Contacts", {trigger: true});
                        break;
                    case "record":
                        break;
                }
            }
            else if( this.module === "Contacts" ) {
                switch( this.viewType ) {
                    case "records":
                        app.router.navigate("#Contacts/create", {trigger: true});
                        break;
                    case "newrecord":
                        app.router.navigate("#Opportunities", {trigger: true});
                        break;
                    case "record":
                        break;
                }
            }
            else if( this.module === "Opportunities" ) {
                switch( this.viewType ) {
                    case "records":
                        app.router.navigate("#Opportunities/create", {trigger: true});
                        break;
                    case "newrecord":
                        this.endTour();
                        return;
                }
            }
            this.initPopover(this.module, this.viewType);
        }
        else {
            var nextIndex = index + 1,
                nextObj = currentArray[nextIndex],
                $nextEl = $("[data-tour='" + nextObj.id + "']"),
                templateEl = '<div class="popover '+ nextObj.id + '"><div class="arrow"></div>' +
                    '<div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content">' +
                    '<p></p></div><div class="modal-footer" style="position: relative;"><a class="btn tour-end">' +
                    app.lang.getAppString("LBL_TOUR_END_TOUR") +'</a><a class="btn btn-primary tour-prev">' +
                    app.lang.getAppString("LBL_TOUR_BACK") +'</a><a class="btn btn-primary tour-next">' +
                    app.lang.getAppString("LBL_TOUR_NEXT") +'</a></div></div></div>';

            // hide the current popover, if it exists
            if( $tourEl.length ) {
                $tourEl.popover("hide");
            }

            // show the next popover, if it exists
            if( $nextEl.length ) {
                // If its not a full tour, don't instruct the user to take certain actions (e.g. click this button to
                // create a new record), this is done by overriding the content with custom "not full tour" content.
                var popoverContent = !(this.fullTour) ? (nextObj["content_not_full"] || nextObj.content) : nextObj.content;

                this.scrollToEl($nextEl, function() {
                    $nextEl.popover({title: nextObj.title, content: popoverContent, placement: nextObj.placement,
                        trigger: "manual", template: templateEl}).popover("show");

                    self.fixPopoverPosition(nextObj.placement, nextObj.id);
                    self.bindClickEvents(self, $nextEl, nextIndex, nextObj, currentArray);
                });
            }
            else {
                this.nextItem(nextIndex, nextObj, currentArray);
            }
        }
    },
    prevItem: function(index, obj, currentArray) {
        var self = this,
            prevIndex = index - 1,
            prevObj = currentArray[prevIndex],
            $tourEl = $("[data-tour='" + obj.id + "']"),
            $prevEl = $("[data-tour='" + prevObj.id + "']");

        if( $tourEl.length ) {
            $tourEl.popover("hide");
        }
        if( $prevEl.length ) {
            this.scrollToEl($prevEl, function() {
                $prevEl.popover("show");
                self.bindClickEvents(self, $prevEl, prevIndex, prevObj, currentArray);
            });
        }
        else {
            this.prevItem(prevIndex, prevObj, currentArray);
        }
    },
    getData: function() {
        var self = this;
        return $.getJSON(app.config.siteUrl + "/clients/base/views/tourguide/data.json", null, function(tourData) {
                if( tourData.error ) {
                    app.alert.show('retrieve_failed', {level: 'error', title:'Tour Failed', messages: 'Failed to retrieve ' +
                        'tour data: '+ tourData.error, autoClose: false});
                }
                else {
                    self.tourData = tourData;
                    if(!self.tourData[self.module]) {
                        app.logger.debug("Tour data does not contain current module: " + self.module);
                    } else if(!self.tourData[self.module][self.viewType]) {
                        app.logger.debug("Tour data does not contain current view: " + self.viewType);
                    } else if(!self.tourData[self.module][self.viewType]["tour_overview_data"]) {
                        app.logger.debug("Tour data does not contain an overview for the current view.");
                    } else {
                        self.overviewData = self.tourData[self.module][self.viewType]["tour_overview_data"];
                    }
                }
        });
    },
    initPopover: function(module, viewType) {
        var self = this,
            list = this.tourData[module][viewType]["tour_popover_data"],
            firstObj = _.first(list),
            $currentEl = $("[data-tour='" + firstObj.id + "']");

        if( $currentEl.length ) {
            var templateEl = '<div class="popover '+ firstObj.id + '"><div class="arrow"></div>' +
                '<div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p>' +
                '</div><div class="modal-footer" style="position: relative;"><a class="btn tour-end">' +
                app.lang.getAppString("LBL_TOUR_END_TOUR") +'</a><a class="btn btn-primary tour-next">' +
                app.lang.getAppString("LBL_TOUR_NEXT") +'</a></div></div></div>';

            this.scrollToEl($currentEl, function() {
                $currentEl.popover({title: firstObj.title, content: firstObj.content, placement: firstObj.placement,
                    trigger: "manual", template: templateEl}).popover("show");

                self.fixPopoverPosition(firstObj.placement, firstObj.id);
                self.bindClickEvents(self, $currentEl, 0, firstObj, list);
            });
        }
        else
        {
            // The first item is not in the DOM, try the next one recursively with nextItem()
            self.nextItem(0, firstObj, list);
        }
    },
    scrollToEl: function($targetEl, callback) {
        var viewportHeight = $(window).height(),
            elTop = $targetEl.offset().top,
            elHeight = $targetEl.height(),
            headerHeight = ($(".navbar").height() + 3) || 48,
            footerHeight = $("footer").height() || 44,

            // the header and footer cover elements on the page so we account for this
            buffer = 55,
            direction;

        if( elTop + elHeight > window.pageYOffset + viewportHeight - footerHeight ) {
            direction = "down";
        }
        // Make the buffer negative if we need to scroll up
        else if( elTop + elHeight < window.pageYOffset + elHeight + headerHeight ) {
            direction = "up";
            buffer *= -1;
        }
        else {
            direction = "none";
            if ( callback && _.isFunction(callback) ) {
                callback();
            }
        }

        if( direction !== "none" ) {
            // scroll to element
            $('body, .main-pane, .side-pane').animate({
                scrollTop: elTop + buffer
            }, function() {
                if ( callback && _.isFunction(callback) ) {
                    callback();
                }
            });
        }
    },
    fixPopoverPosition: function(currentPlacement, className) {
        var documentEl = $(document),
            viewportEl = $(window),
            popoverEl = $("." + className).children(".popover-inner"),
            viewportWidth = viewportEl.width(),
            docHeight = documentEl.height(),
            popoverWidth = popoverEl.width(),
            popoverHeight = popoverEl.height(),
            xyOffset = popoverEl.offset(),
            buffer = 6;

        switch(currentPlacement) {
            // shift left or right
            case "top":
            case "bottom":
                var rightPos = xyOffset.left + popoverWidth;

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
                else if( bottomPos > docHeight ) {
                    var bottomOffset = (bottomPos - docHeight) + buffer;
                    popoverEl.css("position", "relative");
                    popoverEl.css("bottom", bottomOffset);
                }
                break;
        }
        // Currently the z-index for popover is 1010, which hides it under
        // the navbar and the footer. Boost it to 1030 to account for that.
        $("." + className).css("z-index", 1030);
    },
    bindClickEvents: function(scope, $el, index, obj, list) {

        $(".tour-next").on("click", function() {
            scope.nextItem(index, obj, list);
        });
        $(".tour-end").on("click", function() {
            $el.popover("hide");
            scope.endTour();
        });

        if( $(".tour-prev").length ) {
            $(".tour-prev").on("click", function() {
                scope.prevItem(index, obj, list);
            });
        }
    }
})
