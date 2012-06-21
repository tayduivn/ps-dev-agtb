describe("View Manager", function() {
    var app, context;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        context = app.context.getContext();
    });

    afterEach(function() {
        app.view.reset();
    });

    describe("should be able to create class for", function() {

        describe("layout that is", function() {

            it("base", function() {
                var klass = app.view.declareComponent("layout", "list");
                expect(klass).toEqual(app.view.Layout);
            });

            it("typed", function() {

                var klass = app.view.declareComponent("layout", "list", null, null, "fluid");
                expect(klass).toEqual(app.view.layouts.FluidLayout);
            });

            it("named", function() {
                app.view.layouts.MyLayout = app.view.Layout.extend();
                var klass = app.view.declareComponent("layout", "my");
                expect(klass).toEqual(app.view.layouts.MyLayout);
                delete app.view.layouts.MyLayout;
            });

            it("module-specific typed", function() {
                app.view.layouts.ContactsRowsLayout = app.view.Layout.extend();
                var klass = app.view.declareComponent("layout", "detail", "Contacts", null, "rows");
                expect(klass).toEqual(app.view.layouts.ContactsRowsLayout);
                delete app.view.layouts.ContactsRowsLayout;
            });

            it("module-specific named", function() {
                app.view.layouts.AccountsDetailLayout = app.view.Layout.extend();
                var klass = app.view.declareComponent("layout", "detail", "Accounts", null, "fluid");
                expect(klass).toEqual(app.view.layouts.AccountsDetailLayout);
                delete app.view.layouts.AccountsDetailLayout;
            });

            it("module-specific named with controller", function() {
                app.view.declareComponent("layout", "detail", "Accounts", "{ foo: function() {} }");
                expect(app.view.layouts.AccountsDetailLayout).toBeDefined();
                expect(app.view.layouts.AccountsDetailLayout.prototype.foo).toBeDefined();
            });

        });

        describe("view that is", function() {

            it("base", function() {
                var klass = app.view.declareComponent("view", "detail");
                expect(klass).toEqual(app.view.View);
            });

            it("named", function() {
                app.view.views.MyView = app.view.View.extend();
                var klass = app.view.declareComponent("view", "my");
                expect(klass).toEqual(app.view.views.MyView);
                delete app.view.views.MyView;
            });

            it("module-specific", function() {
                app.view.views.AccountsDetailView = app.view.Layout.extend();
                var klass = app.view.declareComponent("view", "detail", "Accounts");
                expect(klass).toEqual(app.view.views.AccountsDetailView);
                delete app.view.views.AccountsDetailView;
            });

            it("module-specific with controller", function() {
                app.view.declareComponent("view", "detail", "Accounts", "{ foo: function() {} }");
                expect(app.view.views.AccountsDetailView).toBeDefined();
                expect(app.view.views.AccountsDetailView.prototype.foo).toBeDefined();
            });

        });

        describe("field that is", function() {

            it("base", function() {
                var klass = app.view.declareComponent("field", "int");
                expect(klass).toEqual(app.view.Field);
            });

            it("named", function() {
                app.view.fields.IntField = app.view.Field.extend();
                var klass = app.view.declareComponent("field", "int");
                expect(klass).toEqual(app.view.fields.IntField);
                delete app.view.fields.IntField;
            });

            it("with controller", function() {
                app.view.declareComponent("field", "int", null, "{ foo: function() {} }");
                expect(app.view.fields.IntField).toBeDefined();
                expect(app.view.fields.IntField.prototype.foo).toBeDefined();
            });

        });

        describe("duplicate components", function() {

            it("declareComponent can be asked to remove duplicates", function() {
                var f1, f2, v1, v2, l1, l2, cache; 

                // Fields
                f1 = app.view.declareComponent("field", "fubar", null, "{ foo: function() { return 'fimpl1'; } }");
                fcheck = app.view.declareComponent("field", "fubar", null, "{ foo: function() { return 'nope'; } }");
                cache = app.view['fields'];

                // Calling declareComponent without the remove param still results in cached component as before
                expect(cache.FubarField.prototype.foo()).toEqual('fimpl1');

                // But if we use the remove param it fubar field will be overwritten
                f2 = app.view.declareComponent("field", "fubar", null, "{ foo: function() { return 'fimpl2'; } }", null, true);
                cache = app.view['fields'];
                expect(cache.FubarField.prototype.foo()).toEqual('fimpl2');

                // Views 
                v1 = app.view.declareComponent("view", "fubar", null, "{ foo: function() { return 'vimpl1'; } }");
                v2 = app.view.declareComponent("view", "fubar", null, "{ foo: function() { return 'vimpl2'; } }", null, true);
                cache = app.view['views'];
                expect(cache.FubarView.prototype.foo()).toEqual('vimpl2');

                // Layouts 
                l1 = app.view.declareComponent("layout", "fubar", null, "{ foo: function() { return 'limpl1'; } }");
                l2 = app.view.declareComponent("layout", "fubar", null, "{ foo: function() { return 'limpl2'; } }", null, true);
                cache = app.view['layouts'];
                expect(cache.FubarLayout.prototype.foo()).toEqual('limpl2');
            });

            it("declareComponent called with remove flag but no controlller is ignored and original preserved", function() {
                var f1, f2, cache; 
                f1 = app.view.declareComponent("field", "fubar", null, "{ foo: function() { return 'original_preserved'; } }");
                f2 = app.view.declareComponent("field", "fubar", null, null /*no controller*/, null, true);
                cache = app.view['fields'];
                expect(cache.FubarField.prototype.foo).toBeDefined();
                expect(cache.FubarField.prototype.foo()).toEqual('original_preserved');
            });
        });

    });

    describe("should be able to create instances of View class which is", function() {

        it('base class', function () {
            var view = app.view.createView({
                name: "edit",
                module: "Contacts",
                context: context
            });

            expect(view instanceof app.view.View).toBeTruthy();
            expect(view.meta).toEqual(fixtures.metadata.modules.Contacts.views.edit.meta);
        });

        it('pre-defined view class', function () {
            var view = app.view.createView({
                name: "list",
                module: "Contacts",
                context: context
            });

            expect(view instanceof app.view.View).toBeTruthy();
        });

        it("custom view class when the view has a custom controller", function () {
            var view = app.view.createView({
                name : "login",
                module: "Home",
                context: context
            });

            expect(view.customCallback).toBeDefined();
            expect(app.view.views.HomeLoginView).toBeDefined();
        });

        it('base class with custom metadata', function() {
            var testMeta = {
                "panels": [
                    {
                        "label": "TEST",
                        "fields": []
                    }
                ]
            };

            var view = app.view.createView({
                name: "edit",
                meta: testMeta,
                context: context
            });

            expect(view instanceof app.view.View).toBeTruthy();
            expect(view.meta).toEqual(testMeta);
        });

        it('custom class without metadata', function() {
            app.view.views.ToolbarView = app.view.View.extend();

            var view = app.view.createView({
                name: "toolbar",
                context: context
            });

            expect(view instanceof app.view.views.ToolbarView).toBeTruthy();
        });

    });

    describe("should be able to create instances of Layout class which is", function() {

        it('base layout class', function () {
            var layout = app.view.createLayout({
                name : "edit",
                module: "Contacts",
                context: context
            });
            expect(layout instanceof app.view.Layout).toBeTruthy();
            expect(layout._components.length).toEqual(1);
            expect(layout._components[0].layout).toEqual(layout);
        });

        it('layout that has a child layout', function () {
            var parent = app.view.createLayout({
                name : "parent",
                context: context
            });

            var child = app.view.createLayout({
                name : "child",
                context: context
            });

            parent.addComponent(child);
            expect(parent._components.length).toEqual(1);
            expect(child.layout).toEqual(parent);

            parent.removeComponent(child);
            expect(child.layout).toBeNull();
            expect(parent._components.length).toEqual(0);
        });

        it("layout with a custom controller", function () {
            var layout;

            app.view.declareComponent("layout", "fluid", null, null, "fluid");
            layout = app.view.createLayout({
                name : "detailplus",
                module: "Contacts",
                context: context
            });

            // Originally checked to see if DetailPlus is a Fluid Layout, however it is no longer the case
            // after migrating layouts serverside, not sure what happened.
            expect(layout instanceof app.view.layouts.ContactsDetailplusLayout).toBeTruthy();
            expect(layout.customLayoutCallback).toBeDefined();
        });

        it("layout with a custom controller passed in params", function () {
            var layout = app.view.createLayout({
                name : "tree",
                context: context,
                controller: "{customTreeLayoutHook: function(){return \"overridden\";}}",
                module: "Contacts"
            });

            expect(layout).toBeDefined();
            expect(layout.customTreeLayoutHook).toBeDefined();
            expect(layout instanceof app.view.layouts.ContactsTreeLayout).toBeTruthy();
        });

        it('layout with custom metadata', function(){
            var layout,
                testMeta = {
                "type" : "simple",
                "module" : "Contacts",
                "components" : [
                    {view : "testComp"}
                ]
            };

            layout = app.view.createLayout({
                context : context,
                name: "edit",
                meta: testMeta
            });

            expect(layout instanceof app.view.Layout).toBeTruthy();
            expect(layout.meta).toEqual(testMeta);
        });

        it('"nameless" layout with inline definition', function() {
            var layout,
                testMeta = {
                "type" : "fluid",
                "components" : [
                    {view : "testComp"}
                ]
            };

            layout = app.view.createLayout({
                context : context,
                meta: testMeta,
                module: "Contacts"
            });

            expect(layout instanceof app.view.layouts.FluidLayout).toBeTruthy();
        });

    });

    describe("should be able to create instances of Field class", function() {

        var bean, collection, view;

        beforeEach(function() {

            //Need a sample Bean
            bean = app.data.createBean("Contacts", {
                first_name: "Foo",
                last_name: "Bar"
            });

            collection = new app.BeanCollection([bean]);

            //Setup a context
            context.set({
                module: "Contacts",
                model: bean,
                collection: collection
            });

            view = new app.view.View({ name: "test", context: context });
        });

        it("with viewdef merged with vardef", function() {
            var def = fixtures.metadata.modules.Cases.views.edit.meta.panels[0].fields[0];
            var ctx = app.context.getContext();
            ctx.set({
                module: "Cases",
                model: app.data.createBean("Cases")
            });
            var result = app.view.createField({
                def: def,
                context: ctx,
                view: new app.view.View({ name: "edit", context: context })
            });

            expect(result instanceof app.view.Field).toBeTruthy();
            expect(result.type).toEqual("float");
            expect(result.name).toEqual("case_number");
            expect(result.label).toEqual("Case Number");
            expect(result.def.round).toEqual(2);
            expect(result.def.precision).toEqual(2);
            expect(result.def.number_group_seperator).toEqual(",");
            expect(result.def.decimal_seperator).toEqual(".");
            expect(result.def.class).toEqual("foo");
        });

        it("should use the vardef controller if provided", function() {
            var def, result, ctx, meta;
            meta = app.metadata.getField('relate'); //has a controller
            def = {
                    'class': "foo",
                    'label': "Relate Label",
                    'name': "relate",
                    'type': "relate"
            };
            ctx = app.context.getContext();
            ctx.set({
                module: "Cases",
                model: app.data.createBean("Cases")
            });
            result = app.view.createField({
                def: def,
                context: ctx,
                meta: meta,
                view: new app.view.View({ name: "edit", context: context })
            });
        });
        

        it("with default template", function() {
            var fieldId = app.view.getFieldId();
            var def = {
                type: 'addresscombo',
                name: "address",
                label: "Address"
            };
            var result = app.view.createField({
                def: def,
                context: context,
                view: view
            });

            expect(result instanceof app.view.Field).toBeTruthy();
            expect(result.type).toEqual("addresscombo");
            expect(result.name).toEqual("address");
            expect(result.label).toEqual("Address");
            expect(result.context).toEqual(context);
            expect(result.def).toEqual(def);
            expect(result.model).toEqual(bean);
            expect(result.sfId).toEqual(fieldId + 1);
            expect(view.fields[result.sfId]).toEqual(result);
        });

        it("of custom class", function() {
            app.view.fields.AddresscomboField = app.view.Field.extend({
                foo: "foo"
            });

            var result = app.view.createField({
                def: {
                    type: 'addresscombo',
                    name: "address",
                    label: "Address"
                },
                context: context,
                view: view
            });

            expect(result instanceof app.view.fields.AddresscomboField).toBeTruthy();
            expect(result.foo).toEqual("foo");
        });

        it("of custom class with controller", function() {
            var result = app.view.createField({
                def: {
                    type: 'text',
                    name: "description"
                },
                context: context,
                view: view
            });

            expect(app.view.fields.TextField).toBeDefined();
            expect(result instanceof app.view.fields.TextField).toBeTruthy();
            expect(result.customCallback).toBeDefined();

            // Checking fall back algorithm
            expect(result.label).toEqual('description');
        });

        it("and use another template than the view name", function() {

            var detailView = new app.view.View({ name: "detail", context: context });
            var opts = {
                def: {
                    type: 'text',
                    name: "name"
                },
                context: context,
                view: detailView,
                viewName: "default" // override template (the "default" template will be used instead of "detail"
            };

            var field = app.view.createField(opts);
            expect(field).toBeDefined();
            field._loadTemplate();

            var ctx = { value: "a value" };

            expect(field.template(ctx)).toEqual(Handlebars.templates["f.text.default"](ctx));
        });

    });

});
