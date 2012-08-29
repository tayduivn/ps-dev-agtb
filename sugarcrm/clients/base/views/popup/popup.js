/**
 *
 * Create a modal popup that renders popup layout container
 * @precondition layout metadata must contain popup into the components.
 * array(
 *      'view' => 'popup'
 *      ),
 * @trigger "app:view:popup:open" Create popup modal window and draws specified type of view
 *      @params module - String Module name (i.e. Accounts, Contacts, etc)
 *      @params type - enum (popup-list, popup-edit, or popup-view)
 *      @params callbak - function
 *
 * @trigger "app:view:popup:callback" Executes binded callback function with the updated model as parameter
 *      @params model - object Backbone model that relates to the current job
 *
 * @trigger ""app:view:popup:close" Close popup modal and release layout for popup
 *
 * How to Use:
 * in the view widget
 *     this.layout.trigger("app:view:popup:open", ...)
 * in the field widget
 *     this.view.layout.trigger("app:view:popup:open", ...)
 */
({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        var popup = this;
        if(this.layout) {
            this.layout.on("app:view:popup:open", function(options, callback) {
                var module = options.module || '',
                    type = options.type || 'popup-list',
                    modelId = options.modelId || '',
                    title = options.title || (type == 'popup-list' ? module : '&nbsp;'),
                    path = module + '/' + type + '/' + modelId,
                    maxHeight = $(window).height() - 300;

                if(app.$rootEl) {
                    maxHeight -= app.$rootEl.children("#header").outerHeight() + app.$rootEl.children("#footer").outerHeight();
                }
                //Modal window setup
                var modal_bg = popup.$el.children(".modal-backdrop");
                if(modal_bg.length == 0) {
                    modal_bg = $("<div>", {'class': 'modal-backdrop'}).hide();
                    this.$el.append(modal_bg);
                }
                modal_bg.show();
                if(app.$contentEl && $("body").hasClass("modal-open") == false) {
                    popup.$styleEl = $("<style>").text('#content { top: ' + ($(window).scrollTop() * -1) + 'px; position:fixed; width:100%; }')
                    this.$el.append(
                        popup.$styleEl
                    );
                }

                var modal_container = this.$(".modal:first").attr("style", "").show();
                $("body").addClass("modal-open");

                //Render body
                //Choose proper layout type
                var layoutType = 'list'; //default
                if(type == 'popup-edit') {
                    layoutType = 'edit';
                } else if(type == 'popup-detail') {
                    layoutType = 'detail';
                }
                //Resizing the default modal to fix with window size
                //TODO: Should replace it with CSS later
                if(layoutType == 'list') {
                    modal_container.addClass('span11').css({
                        'margin' : '0',
                        'left' : '4.255%',
                        'top' : '5%',
                        'max-height' : 'none'
                    });
                    modal_container.children(".modal-body").css({
                        'padding': '0'
                    });
                    modal_container.children(".modal-footer").hide();
                } else {
                    modal_container.removeClass('span11').css({
                        'margin-top' : '0',
                        'margin-bottom' : '0',
                        'top' : '5%',
                        'max-height' : 'none'
                    });

                    modal_container.children(".modal-body").css({
                        'padding': ''
                    });
                    if(layoutType == 'edit')
                        modal_container.children(".modal-footer").show();
                    else
                        modal_container.children(".modal-footer").hide();
                }

                //Render the container if and only if the requested container is different from previous existed container
                if(!popup._previousModule || popup._previousModule != path) {
                    var context = app.context.getContext();
                    popup._previousModule = path;
                    //set default parameters and initialize context
                    context.set({
                        layout:layoutType,
                        action:type,
                        module: module,
                        modelId: modelId
                    }).prepare();

                    //create layout
                    var layout = app.view.createLayout({
                        name: type,
                        module: module,
                        context: context
                    });

                    //Render the wireframe
                    var _popupDom = popup.$(".modal-body:first").html(layout.$el);

                    //TODO: Resize the window size to fix with window size
                    if(maxHeight > 400) {
                        _popupDom.css("max-height", maxHeight);
                    }


                    //Set title of the popup window
                    //TODO: Acl roles for quick creation
                    //TODO: Replace inline CSS with class name
                    popup.$(".modal-header:first .title").html(title);
                    popup.$(".header .buttons").children().not(":first").remove();
                    if(type == 'popup-list') {
                        //app.acl.hasAccessToModel('edit', popup.model)

                        popup.$(".header .buttons").append(
                            $("<a>", {
                                'href' : '#',
                                'class' : 'close search',
                                'rel' : 'tooltip',
                                //TODO: Replace with App string
                                'data-original-title' : 'Search'
                            }).css("margin-right", "10px").html('<i class="icon-search icon-md"></i>').click(function(evt) {
                                    evt.preventDefault();
                                    layout.trigger("list:search:toggle", evt);
                                }).tooltip({
                                    placement: "bottom"
                                }),
                            $("<a>", {
                                href: '#',
                                class: 'close',
                                'rel' : 'tooltip',
                                'data-original-title' : app.lang.get('LNK_CREATE', module)
                            }).css("margin-right", "10px").html('<i class="icon-plus icon-md"></i>').click(function(evt){
                                    evt.preventDefault();
                                    layout.trigger("list:filter:popupEditor", evt);
                                }).tooltip({
                                    placement: "bottom"
                                })
                        );
                    }

                    //Bind event handler to catch the actions within the popup window
                    layout.on("app:view:popup:callback", function(model, afterClose) {
                        var self = this,
                            close = _.isBoolean(afterClose) ? afterClose : true;
                        callback.call(self, model);
                        if(close)
                            popup.close();
                    });
                    layout.on("app:view:popup:close", function() {
                        popup.close();
                    });
                    layout.on("app:view:popup:setButtons", function(dom) {
                        popup.$(".modal-footer").children().remove();
                        dom.find(".btn").each(function(index){
                            var button = $(this);
                            popup.$(".modal-footer").append(
                                button.clone().click(function(evt){
                                    evt.preventDefault();
                                    button.click();
                                }));
                        });

                    });
                    layout.render();
                    layout.loadData(); //Server Call
                    if(!app.controller.popupLayouts) {
                        app.controller.popupLayouts = {};
                    }
                    delete popup.popupLayout;
                    popup.popupLayout = layout;
                }
                //Keep the layout pointer in the global controller
                app.controller.popupLayouts[popup.popupLayout.cid] = popup.popupLayout;
            }, this);
            //bind one of the popup trigger into the global controller
            if(!(app.controller._callbacks && app.controller._callbacks["app:view:popup:open"])) {
                app.controller.on("app:view:popup:open", function(options, callback) {
                    popup.layout.trigger("app:view:popup:open",options, callback);
                }, popup);
            }
        }
    },
    _renderHtml: function() {
        var self = this;
        app.view.View.prototype._renderHtml.call(this);
        this.$(".modal-header:first .close[data-dismiss=modal]").click(function(evt){
            self.close.call(self, evt);
        }).tooltip({
                placement: "bottom"
            });
    },
    close: function(event) {
        //deallocate the popup pointer
        if(this.popupLayout) {
            app.controller.popupLayouts[this.popupLayout.cid] = null;
            delete app.controller.popupLayouts[this.popupLayout.cid];
            //restore back to the scroll position at the top
            this.$(".modal-body:first").scrollTop(0);
            if(_.size(app.controller.popupLayouts) == 0) {
                $('body').removeClass("modal-open");
                this.releaseModal();
            }
            this.$(".modal:first").hide();
            this.$el.children(".modal-backdrop").hide();
        }
    },
    releaseModal: function() {
        var _top = parseInt(app.$contentEl.css("top").replace("px", "")) * -1;
        if(this.$styleEl) {
            this.$styleEl.remove();
        }
        $(window).scrollTop(_top);
    }
})