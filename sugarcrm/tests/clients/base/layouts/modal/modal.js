describe("Base.Layout.Modal", function() {
    var app, view, context, ModalLayout, layout;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        if (!app.view.layouts.ModalLayout)
        {
            SugarTest.loadComponent("base", "layout", "modal");
        }

        if (!app.view.views.ModalHeaderView)
        {
            SugarTest.loadComponent("base", "view", "modal-header");
        }

        if (!app.view.views.ModalConfirmView)
        {
            SugarTest.loadComponent("base", "view", "modal-confirm");
        }
        if (!$.fn.modal) {
            $.fn.modal = function(options) {};
        }
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        $.fn.modal = null;
        layout.context = null;
        layout = null;
    });

    it("should delegate triggers at contruction time", function(){
        var definedTriggerName = 'app:layout:modal:open1',
            calledEventName = '',
            options = {
                'showEvent' : definedTriggerName
            },
            parent = {
                on: function(event) {
                    calledEventName = event;
                    sinon.stub();
                }
            };
        sinon.spy(parent, "on");
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        expect(calledEventName).toEqual(definedTriggerName);
        expect(parent.on).toHaveBeenCalledOnce();
        expect(parent.on.calledWith(calledEventName)).toBe(true);
    });

    it("should delegate multiple trigger names for showevent", function(){
        var definedTriggerName = ['editpopup', 'detailpopup'],
            calledEventName = '',
            options = {
                'showEvent' : definedTriggerName
            },
            parent = {
                on: function(event) {
                    calledEventName = event;
                    sinon.stub();
                }
            };

        sinon.spy(parent, "on");
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        expect(parent.on.calledWith('editpopup')).toBe(true);
        expect(parent.on.calledWith('detailpopup')).toBe(true);

    });

    it("should build proper modal dom elements", function(){
        var definedTriggerName = 'app:layout:modal:open',
            calledEventName = '',
            options = {
                'showEvent' : definedTriggerName
            },
            parent = {
                on: function(event) {
                    calledEventName = event;
                    sinon.stub();
                }
            };
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        expect(layout.$(".modal").length).toEqual(1);
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
                'showEvent' : definedTriggerName
            },
            parent = {
                on: function(event, caller) {
                    calledEventName = event;
                    calledCaller = caller;
                }
            },
            calledModule = 'Accounts';
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
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
                'showEvent' : definedTriggerName
            },
            parent = {
                on: function(event, caller) {
                    calledEventName = event;
                    calledCaller = caller;
                }
            },
            message = 'blahblah',
            title = 'poo title';
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
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

    it("should adjust the modal width size", function() {
        var definedTriggerName = 'app:layout:modal:open',
            options = {
                'showEvent' : definedTriggerName,
            },
            parent = {
                on: function(event, caller) {
                }
            },
            confirmDialog = {
                'message' : 'blah',
                'title' : 'title'
            };
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        layout.show(_.extend({width: 4}, confirmDialog));
        expect(layout.$(".modal").width()).toBe(4);

        layout.show(_.extend({width: 5}, confirmDialog));
        expect(layout.$(".modal").width()).toBe(5);
        expect(layout.$(".modal").width()).not.toBe(4);

        layout.show(_.extend({}, confirmDialog));
        expect(layout.$(".modal").width()).not.toBe(5);
        expect(layout.$(".modal").width()).not.toBe(4);
    });


    it("should invoke before/after while modal is showing and hiding", function() {
        var definedTriggerName = 'app:layout:modal:open',
            options = {
                'showEvent' : definedTriggerName,
                'components' : [ { view: 'blah' }]
            },
            parent = {
                on: function(event, caller) {
                }
            };
        layout = app.view.createLayout({
            name : "modal",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        layout.triggerBefore = function(event) {
            sinon.stub();
        };

        layout.trigger = function(event) {
            sinon.stub();
        };
        var showOptions = {'blah' : 'yeahhh'};
        sinon.spy(layout, "triggerBefore");
        sinon.spy(layout, "trigger");

        layout.show({options: showOptions});
        expect(layout.triggerBefore.calledWith('show')).toBe(true);
        expect(layout.triggerBefore.calledWith('hide')).toBe(false);
        layout.hide();
        expect(layout.triggerBefore.calledWith('hide')).toBe(true);
    });
});