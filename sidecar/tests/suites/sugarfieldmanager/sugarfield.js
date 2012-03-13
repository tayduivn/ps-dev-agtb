describe("SugarField", function() {
    it("should delegate events", function() {
        var delegateSpy = sinon.spy(Backbone.View.prototype, 'delegateEvents');
        var events = {"click": "callback_click"};
        var bean = new Backbone.Model();

        var view = {};
        var context = {};
        var inputEvents = fixtures.metadata.Cases.views.editView.buttons[0].events;
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
        var bean = new Backbone.Model();
            bean.set("status","new");
        var view = {};
        var context = {};
        var inputEvents = fixtures.metadata.Cases.views.editView.buttons[0].events;
        var field = SUGAR.App.sugarFieldManager.get({
            def: {name: "status", type: "varchar"},
            view: view,
            context: context,
            model: bean
        });

        field.render();

        expect(field.$el.html()).toEqual('<span name="status">new</span>');
    });

    it("should bind change on render", function(){
        var bean = new Backbone.Model();
            bean.set("status","new");
        var view = {};
        var context = {};
        var inputEvents = fixtures.metadata.Cases.views.editView.buttons[0].events;
        var field = SUGAR.App.sugarFieldManager.get({
            def: {name: "status", type: "varchar"},
            view: view,
            context: context,
            model: bean
        });

        var modelOnSpy = sinon.spy(field.model, "on");

        field.render();

        expect(modelOnSpy).toHaveBeenCalled();
    });

    it("unbind events", function() {
        var bean = new Backbone.Model();
            bean.set("status","new");
        var view = {};
        var context = {bob:"bob"};
        var inputEvents = fixtures.metadata.Cases.views.editView.buttons[0].events;
        var field = SUGAR.App.sugarFieldManager.get({
            def: {name: "status", type: "varchar"},
            view: view,
            context: context,
            model: bean
        });

        field.unBind();

        expect(field.model).toBeUndefined();
        expect(field.context).toBeUndefined();
    });


    it("bind events", function() {
        var bean = new Backbone.Model();
            bean.set("status","new");
        var secondBean = new Backbone.Model();
            secondBean.set("status","old");
        var view = {};
        var context = {bob:"bob"};
        var secondContext = {rob:"rob"};
        var inputEvents = fixtures.metadata.Cases.views.editView.buttons[0].events;
        var field = SUGAR.App.sugarFieldManager.get({
            def: {name: "status", type: "varchar"},
            view: view,
            context: context,
            model: bean
        });

        field.render();

        field.bind(secondContext,secondBean);
        secondBean.set("status","older");

        expect(field.$el.html()).toEqual('<span name="status">older</span>');
        expect(field.model).toEqual(secondBean);
        expect(field.context).toEqual(secondContext);
    });
});