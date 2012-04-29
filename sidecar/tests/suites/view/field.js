describe("Field", function() {

    var app, bean, meta = fixtures.metadata, view;

    beforeEach(function() {
        app = SugarTest.app;
        app.template.initialize();
        app.template.load(meta.viewTemplates);
        app.metadata.set(meta);
        app.data.declareModel("Cases", fixtures.metadata.modules.Cases);
        bean = app.data.createBean("Cases");
        view = new app.view.View({ name: "test" });
    });

     afterEach(function() {
         app.cache.cutAll();
         delete Handlebars.templates;
     });

    it("should delegate events", function() {
        var delegateSpy = sinon.spy(Backbone.View.prototype, 'delegateEvents'),
            events = {"click": "callback_click"},
            bean = new Backbone.Model(),
            context = {},
            inputEvents = fixtures.metadata.modules.Cases.views.edit.buttons[0].events,
            field = app.view.createField({
                def: { name: "status", type: "varchar" },
                view: view,
                context: context,
                model: bean
            });

        field.delegateEvents(inputEvents);
        expect(delegateSpy).toHaveBeenCalledWith(events);
        delegateSpy.restore();
    });

    it("should render sugarfields html", function() {
        var context = {},
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
        var context = {},
            field = app.view.createField({
                def: {name: "status", type: "text"},
                view: view,
                context: context,
                model: bean
            }), 
            spy = sinon.spy(field, 'bindDomChange');

        bean.set({status: "new", id: "anId"});

        expect(spy).toHaveBeenCalled();
    });

    it("unbind events", function() {
        var context = {bob: "bob"},
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
        var id = _.uniqueId('sugarFieldTest'),
            bean, context, field, input;

        $('body').append('<div id="'+id+'"></div>');
        bean = new Backbone.Model();
        var view = new app.view.View({name: 'edit'});
        context = {bob:"bob"};
        field = app.view.createField({
            def: {name: "status", type: "varchar"},
            view: view,
            context: context,
            model: bean,
            el:$('#'+id)
        });

        bean.set({status: "new"});
        input = field.$el.find("input");
        input.attr('value','bob');
        input.trigger('change');
        expect(bean.get('status')).toEqual('bob');
        $('#'+id).remove();
    });
});

