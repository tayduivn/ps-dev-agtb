describe("Base.Layout.Modal", function() {
    var app, view, context, bean, ModalLayout, PopupLayout;

    beforeEach(function() {
        app = SugarTest.app;
        app.controller = {};
        //app.metadata.set(meta);
        context = app.context.getContext();
        if (!app.view.layouts.ModalLayout)
        {
            $.ajax("../clients/base/layouts/modal/modal.js", {
                async : false,
                success : function(o) {
                    ModalLayout = app.view.declareComponent("layout", "modal", null, o, null, true);
                }
            });
        }
    });

    afterEach(function() {
        app.cache.cutAll();
        delete Handlebars.templates;
    });

    it("should exists", function() {
        expect(app.view.layouts.ModalLayout).toBeDefined();
    });

    it("should delegate triggers at contruction time", function(){
        var definedTriggerName = 'app:layout:modal:open',
            calledEventName = '',
            options = {
                'meta' : {
                    'showEvent' : definedTriggerName
                },
                'context' : context,
                'layout' : {
                    on: function(event) {
                        calledEventName = event;
                        sinon.stub();
                    }
                }
            };
        sinon.spy(options.layout, "on");
        sinon.spy(context, "on");
        var layout = new ModalLayout(options);
        expect(calledEventName).toEqual(definedTriggerName);
        expect(layout.showEvent).toBe(definedTriggerName);
        expect(options.layout.on).toHaveBeenCalledOnce();
        expect(options.layout.on.calledWith(definedTriggerName)).toBe(true);
        expect(context.on.calledWith('modal:open')).toBe(true);
        expect(context.on).toHaveBeenCalledOnce();
    });

    it("should delegate multiple trigger names for showevent", function(){
        var definedTriggerName = ['editpopup', 'detailpopup'],
            calledEventName = '',
            options = {
                'meta' : {
                    'showEvent' : definedTriggerName
                },
                'context' : context,
                'layout' : {
                    on: function(event) {
                        calledEventName = event;
                        sinon.stub();
                    }
                }
            };

        sinon.spy(options.layout, "on");
        var layout = new ModalLayout(options);
        expect(options.layout.on.calledWith('editpopup')).toBe(true);
        expect(options.layout.on.calledWith('detailpopup')).toBe(true);

    });

    it("should build proper modal dom elements", function(){
        var definedTriggerName = 'app:layout:modal:open',
            calledEventName = '',
            options = {
                'meta' : {
                    'showEvent' : definedTriggerName
                },
                'context' : context,
                'layout' : {
                    on: function(event) {
                        calledEventName = event;
                        sinon.stub();
                    }
                }
            };
        var layout = new ModalLayout(options);
        expect(layout.$(".modal").length).toEqual(1);
        expect(layout.$(".modal-backdrop").length).toEqual(1);
        expect(layout.$(".modal-body").length).toEqual(0);
        var comp = {
            el: 'foo container'
        }
        layout._placeComponent(comp, { layout: 'popup-list', bodyComponent: true});
        expect(layout.$(".modal-body").length).toEqual(1);
        expect(layout.$(".modal-body").html()).toEqual('foo container');
    });


    it("should bind modal-body container", function(){
        var definedTriggerName = 'app:layout:modal:open',
            calledEventName = '',
            calledCaller = null,
            options = {
                'meta' : {
                    'showEvent' : definedTriggerName
                },
                'context' : context,
                'layout' : {
                    on: function(event, caller) {
                        calledEventName = event;
                        calledCaller = caller;
                    }
                }
            },
            calledModule = 'Accounts';
        var layout = new ModalLayout(options);
        var comp = {},
            def = {};
        layout._placeComponent(comp, def);

        //Add one layout component
        calledCaller.call(layout, {
            components: [ {layout: 'popup-list'} ],
            context: { module: calledModule }
        });

        expect(layout._components.length).toEqual(layout._initComponentSize + 1);
        expect(layout._components[layout._initComponentSize].module).toEqual(calledModule);
        expect(_.has(layout.context._callbacks, 'modal:callback')).toBe(true);
        expect(_.has(layout.context._callbacks, 'modal:close')).toBe(true);
        expect(_.has(layout.context._callbacks, 'modal:undefined')).toBe(false);

        //Add two components
        calledCaller.call(layout, {
            components: [ {layout: 'test-list'}, {view: 'test'} ],
            context: { module: calledModule }
        });
        //it should clean out the previous components and append only new components
        expect(_.find(layout._components, function(component) {
            return (component.options.name == 'popup-list');
        })).toBeFalsy();
        expect(layout._components.length).toEqual(layout._initComponentSize + 2);
    });

    it("should create modal-body container with simple parameters", function(){
        var definedTriggerName = 'app:layout:modal:open',
            calledEventName = '',
            calledCaller = null,
            options = {
                'meta' : {
                    'showEvent' : definedTriggerName
                },
                'context' : context,
                'layout' : {
                    on: function(event, caller) {
                        calledEventName = event;
                        calledCaller = caller;
                    }
                }
            },
            calledModule = 'Accounts',
            calledModelId = '123-123-222';
        var layout = new ModalLayout(options);
        var comp = {},
            def = {};
        layout._placeComponent(comp, def);

        calledCaller.call(layout, {
            layout: 'popup-list',
            module: calledModule
        });
        expect(layout._components[layout._initComponentSize].module).toEqual(calledModule);

        calledCaller.call(layout, {
            layout: 'popup-list',
            module: calledModule,
            modelId: calledModelId
        });
        expect(layout._components[layout._initComponentSize].context.get("modelId")).toEqual(calledModelId);
    });

    it("should create a simple modal dialog", function(){
        var definedTriggerName = 'app:layout:modal:open',
            calledEventName = '',
            calledCaller = null,
            options = {
                'meta' : {
                    'showEvent' : definedTriggerName
                },
                'context' : context,
                'layout' : {
                    on: function(event, caller) {
                        calledEventName = event;
                        calledCaller = caller;
                    }
                }
            },
            message = 'blahblah',
            title = 'poo title';
        var layout = new ModalLayout(options);
        var comp = {},
            def = {};
        calledCaller.call(layout, {
            title: title,
            message: message
        });

        expect(layout.context.get("title")).toEqual(title);
        expect(_.first(layout.getBodyComponents()).context.get("message")).toEqual(message);
        expect(_.first(layout.getBodyComponents()).name).toEqual("modal-confirm");
    });

    it("should adjust the modal span size", function() {
        var definedTriggerName = 'app:layout:modal:open',
            options = {
                'meta' : {
                    'showEvent' : definedTriggerName
                },
                'context' : context,
                'layout' : {
                    on: function(event, caller) {
                    }
                }
            };
        var layout = new ModalLayout(options);
        layout.show(4);
        expect(layout.$(".modal").hasClass("span4")).toBe(true);

        layout.show(5);
        expect(layout.$(".modal").hasClass("span4")).toBe(false);
        expect(layout.$(".modal").hasClass("span5")).toBe(true);

        layout.show();
        expect(layout.$(".modal").hasClass("span4")).toBe(false);
        expect(layout.$(".modal").hasClass("span5")).toBe(false);
    });

});