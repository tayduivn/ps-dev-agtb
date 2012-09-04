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
        expect(layout.$(".modal-body").length).toEqual(1);
        var comp = {
            el: 'foo container'
        }
        layout._placeComponent(comp, { layout: 'popup-list'});
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
        expect(layout.getPopupComponent()).toBe(null);

        calledCaller.call(layout, {
            module: calledModule,
            layout: 'popup-list'
        });
        expect(layout.getPopupComponent().module).toEqual(calledModule);
        expect(_.has(layout.getPopupComponent()._callbacks, 'modal:callback')).toBe(true);
        expect(_.has(layout.getPopupComponent()._callbacks, 'modal:close')).toBe(true);
        expect(_.has(layout.getPopupComponent()._callbacks, 'modal:undefined')).toBe(false);
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