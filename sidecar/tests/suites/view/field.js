describe("Field", function() {

    var app, bean;

    beforeEach(function() {
        app = SugarTest.app;
        app.data.declareModel("Cases", fixtures.metadata.modules.Cases);
        bean = app.data.createBean("Cases");
    });

    it("should delegate events", function() {
        var delegateSpy = sinon.spy(Backbone.View.prototype, 'delegateEvents');
        var events = {"click": "callback_click"};

        var view = {};
        var context = {};
        var inputEvents = fixtures.metadata.modules.Cases.views.edit.buttons[0].events;
        var field = app.view.createField({
            def: { name: "status", type: "text" },
            view: view,
            context: context,
            model: bean
        });

        field.delegateEvents(inputEvents);
        expect(delegateSpy).toHaveBeenCalledWith(events);
        delegateSpy.restore();
    });

    it("should render sugarfields html", function() {
        var view = {},
            context = {},
            field = app.view.createField({
                def: {name: "status", type: "text"},
                view: view,
                context: context,
                model: bean
            });

        bean.set({status: "new", id: "anId"});
        field.render();

        expect(field.$el.html()).toEqual('<span name="status">new</span>');
    });

    it("should bind bind model change on render", function() {
        var view = {},
            context = {},
            field = app.view.createField({
                def: {name: "status", type: "text"},
                view: view,
                context: context,
                model: bean
            });

        var spy = sinon.spy(field, 'bindDomChange');

        bean.set({status: "new", id: "anId"});

        expect(spy).toHaveBeenCalled();
    });

    it("unbind events", function() {
        var view = {},
            context = {bob: "bob"},
            inputEvents = fixtures.metadata.modules.Cases.views.edit.buttons[0].events,
            field = app.view.createField({
                def: {name: "status", type: "text"},
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
        var secondBean = app.data.createBean("Cases"),
            view = {},
            context = {bob:"bob"},
            secondContext = {rob:"rob"},
            field = app.view.createField({
                def: {name: "status", type: "text"},
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
        var view = {name:'edit'},
            context = {bob:"bob"},
            field = app.view.createField({
                def: {name: "status", type: "text"},
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
