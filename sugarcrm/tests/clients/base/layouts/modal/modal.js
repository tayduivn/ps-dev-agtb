describe("Base.Layout.Modal", function() {
    var app, view, context, ModalLayout, layout;

    beforeEach(function() {
        app = SugarTest.app;
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

        if (!app.view.views.ModalHeaderView)
        {
            $.ajax("../clients/base/views/modal-header/modal-header.js", {
                async : false,
                success : function(o) {
                    app.view.declareComponent("view", "modal-header", null, o, null, true);
                }
            });
        }

        if (!app.view.views.ModalConfirmView)
        {
            $.ajax("../clients/base/views/modal-confirm/modal-confirm.js", {
                async : false,
                success : function(o) {
                    app.view.declareComponent("view", "modal-confirm", null, o, null, true);
                }
            });
        }
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        layout.context = null;
        layout = null;
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
        layout = new ModalLayout(options);
        expect(calledEventName).toEqual(definedTriggerName);
        expect(layout.showEvent).toBe(definedTriggerName);
        expect(options.layout.on).toHaveBeenCalledOnce();
        expect(options.layout.on.calledWith(calledEventName)).toBe(true);
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
        layout = new ModalLayout(options);
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
        layout = new ModalLayout(options);
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
        layout = new ModalLayout(options);
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
        layout = new ModalLayout(options);
        var comp = {},
            def = {};
        calledCaller.call(layout, {
            title: title,
            message: message
        });

        expect(layout.getComponent("modal-header").title).toEqual(title);
        expect(_.first(layout.getBodyComponents()).message).toEqual(message);
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
        layout = new ModalLayout(options);
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