describe("Base.Field.QuickCreate", function() {
    var app, field, drawerBefore, event, alertShowStub, alertConfirm, mockDrawerCount, collection, spyOnFetch;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'field', 'quickcreate');
        field = SugarTest.createField("base","quickcreate", "quickcreate", "quickcreate");
        alertConfirm = false;
        mockDrawerCount = 0;

        alertShowStub = sinon.stub(app.alert, 'show', function(name, options) {
            if (alertConfirm) options.onConfirm();
        });

        drawerBefore = app.drawer;
        app.drawer = {
            count: function() {
                return mockDrawerCount;
            },
            reset: sinon.stub(),
            open: sinon.stub()
        };

        event = {
            currentTarget: '<a data-module="Foo" data-layout="Bar"></a>'
        };

        collection = {
            module: "Foo",
            fetch: function(){}
        };
        spyOnFetch = sinon.spy(collection, 'fetch');
    });

    afterEach(function() {
        alertShowStub.restore();
        spyOnFetch.restore();
        app.drawer = drawerBefore;
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    it('should open the drawer without confirm if no drawers open', function() {
        var drawerOptions;
        field._handleActionLink(event);
        drawerOptions = _.first(app.drawer.open.lastCall.args);

        expect(alertShowStub.callCount).toBe(0);
        expect(drawerOptions.context.module).toEqual('Foo');
        expect(drawerOptions.layout).toEqual('Bar');
    });

    it('should show confirmation when drawers are open and not open drawer if not confirmed', function() {
        alertConfirm = false;
        mockDrawerCount = 1;
        field._handleActionLink(event);

        expect(alertShowStub.callCount).toBe(1);
        expect(app.drawer.reset.callCount).toBe(0);
        expect(app.drawer.open.callCount).toBe(0);
    });

    it('should reset drawers and open new drawer if confirmed', function() {
        alertConfirm = true;
        mockDrawerCount = 2;
        field._handleActionLink(event);

        expect(alertShowStub.callCount).toBe(1);
        expect(app.drawer.reset.callCount).toBe(1);
        expect(app.drawer.open.callCount).toBe(1);
    });

    it('should refresh collection for current app context if it is same module', function() {
        alertConfirm = true;
        mockDrawerCount = 1;
        app.drawer.open = function(options, callback){ callback(true); };

        app.controller.context.set("collection", collection);
        field._handleActionLink(event);
        expect(spyOnFetch).toHaveBeenCalled();
        app.controller.context.unset("collection");
    });

    it('should refresh collection(s) for child contexts if it is same module', function() {
        alertConfirm = true;
        mockDrawerCount = 1;
        app.drawer.open = function(options, callback){ callback(true); };
        var child = new Backbone.Model();

        child.set("collection", collection);
        app.controller.context.children = [child];
        field._handleActionLink(event);
        expect(spyOnFetch).toHaveBeenCalled();
        app.controller.context.children = [];
    });
});
