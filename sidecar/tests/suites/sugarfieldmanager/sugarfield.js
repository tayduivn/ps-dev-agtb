describe("SugarField", function() {
    it("should delegate events", function() {
        var delegateSpy = sinon.spy(Backbone.View.prototype, 'delegateEvents');
        var events = {"click": "callback_click"};
        var bean = new Backbone.Model();

        var view = {};
        var context = {};
        var inputEvents = fixtures.metadata.modules.Cases.views.editView.buttons[0].events;
        var field = SUGAR.App.sugarFieldManager.get({
            def: {name: "status", type: "varchar"},
            view: view,
            context: context,
            model: bean
        });
        field.delegateEvents(inputEvents);
        expect(delegateSpy).toHaveBeenCalledWith(events);
        delegateSpy.restore();
    });

    it("should render sugarfields html", function() {
        var bean = new Backbone.Model(),
            view = {},
            context = {},
            field = SUGAR.App.sugarFieldManager.get({
                def: {name: "status", type: "varchar"},
                view: view,
                context: context,
                model: bean
            });

        bean.set({status: "new", id: "anId"});
        field.render();

        expect(field.$el.html()).toEqual('<span name="status">new</span>');
    });

    it("should bind change on render", function() {
        var bean = new Backbone.Model(),
            view = {},
            context = {},
            field = SUGAR.App.sugarFieldManager.get({
                def: {name: "status", type: "varchar"},
                view: view,
                context: context,
                model: bean
            });


        var modelOnSpy = sinon.spy(field.model, "on");

        bean.set({status: "new", id: "anId"});
        field.render();

        expect(modelOnSpy).toHaveBeenCalled();
    });

    it("unbind events", function() {
        var bean = new Backbone.Model(),
            view = {},
            context = {bob: "bob"},
            inputEvents = fixtures.metadata.modules.Cases.views.editView.buttons[0].events,
            field = SUGAR.App.sugarFieldManager.get({
                def: {name: "status", type: "varchar"},
                view: view,
                context: context,
                model: bean
            });

        bean.set({status: "new", id: "anId"});
        field.unBind();

        expect(field.model).toBeUndefined();
        expect(field.context).toBeUndefined();
    });


    it("bind events", function() {
        var bean = new Backbone.Model(),
            secondBean = new Backbone.Model(),
            view = {},
            context = {bob:"bob"},
            secondContext = {rob:"rob"},
            field = SUGAR.App.sugarFieldManager.get({
                def: {name: "status", type: "varchar"},
                view: view,
                context: context,
                model: bean
            });


        bean.set({status: "new", id: "anId"});
        secondBean.set({status: "old", id: "anotherId"});
        field.render();

        field.bind(secondContext,secondBean);
        secondBean.set("status","older");

        expect(field.$el.html()).toEqual('<span name="status">older</span>');
        expect(field.model).toEqual(secondBean);
        expect(field.context).toEqual(secondContext);
    });
});