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

    it("should bind bind model change on render", function() {
        var bean = new Backbone.Model(),
            view = {},
            context = {},
            field = SUGAR.App.sugarFieldManager.get({
                def: {name: "status", type: "varchar"},
                view: view,
                context: context,
                model: bean
            });

        var spy = sinon.spy(field, 'bindDomChange');

        bean.set({status: "new", id: "anId"});

        expect(spy).toHaveBeenCalled();
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


    it("bind render to model change events", function() {
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

        field.bindModelChange(secondContext,secondBean);
        secondBean.set("status","older");

        expect(field.$el.html()).toEqual('<span name="status">older</span>');
        expect(field.model).toEqual(secondBean);
        expect(field.context).toEqual(secondContext);
    });

    it("update model on dom input change", function() {
        var id = _.uniqueId('sugarFieldTest');
        $('body').append('<div id="'+id+'"></div>');
        var bean = new Backbone.Model(),
            view = {name:'editView'},
            context = {bob:"bob"},
            field = SUGAR.App.sugarFieldManager.get({
                def: {name: "status", type: "varchar"},
                view: view,
                context: context,
                model: bean,
                el:$('#'+id)
            });
        bean.set({status: "new"});
        var input = field.$el.find("input");
        input.attr('value','bob');
        input.trigger('change');
        expect(bean.get('status')).toEqual('bob');
        $('#'+id).remove();
    });
});