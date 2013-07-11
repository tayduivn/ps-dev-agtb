describe("Base.Field.Base", function() {
    var app, field, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        app.view.Field.prototype._renderHtml = function() {};
        sinonSandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field.model = null;
        field._loadTemplate = null;
        field = null;
        sinonSandbox.restore();
    });

    it('should bind custom event handlers', function() {
        var def = {
            'events' : {
                'click a[href]' : 'function() { this.callback = "stuff excuted"; }',
                'blur a[href]' : 'function() { this.callback = "blur excuted"; }'
            }
        };

        field = SugarTest.createField("base","button", "base", "list", def);
        field._loadTemplate = function() { this.template = function(){ return '<a href="javascript:void(0);"></a>'}; };
        field.render();
        field._renderHtml();

        expect(field.callback).toBeUndefined();
        field.$("a[href]").trigger('click');
        expect(field.callback).toBe("stuff excuted");
        field.$("a[href]").trigger('blur');
        expect(field.callback).not.toBe("stuff excuted");
        expect(field.callback).toBe("blur excuted");
        delete field.callback;
        field.$("a[href]").trigger('undefined');
        expect(field.callback).not.toBe("stuff excuted");
        expect(field.callback).not.toBe("blur excuted");
        expect(field.callback).toBeUndefined();
    });

    it('should trim whitespace on unformat', function(){
        field = SugarTest.createField("base","button", "base", "list");
        expect(field.unformat("  ")).toEqual("");
        expect(field.unformat("")).toEqual("");
        expect(field.unformat(" abc   ")).toEqual("abc");
        expect(field.unformat(123)).toEqual(123);
        expect(field.unformat({})).toEqual({});
    });

    it('should create bwc if defined on module', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule', function() {
            return {isBwcEnabled: true};
        });
        var def = { link: true };
        field = SugarTest.createField('base', 'text', 'base', 'list', def);
        field.model = new Backbone.Model({id: '12345', module: 'Quotes'});
        field._render();
        expect(getModuleStub).toHaveBeenCalled();
        expect(field.href).toEqual('#bwc/index.php?module=Quotes&action=DetailView&record=12345');
    });

    it('should create bwc if defined on def', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule', function() {
            return {isBwcEnabled: false};
        });
        var def = { link: true, bwcLink: true };
        field = SugarTest.createField('base', 'text', 'base', 'list', def);
        field.model = new Backbone.Model({id: '12345', module: 'Quotes'});
        field._render();
        expect(getModuleStub).toHaveBeenCalled();
        expect(field.href).toEqual('#bwc/index.php?module=Quotes&action=DetailView&record=12345');
    });

    it('should not create bwc if defined false on def', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule', function() {
            return {isBwcEnabled: false};
        });
        var def = { link: true, bwcLink: false };
        field = SugarTest.createField('base', 'text', 'base', 'list', def);
        field.model = new Backbone.Model({id: '12345', module: 'Quotes'});
        field._render();
        expect(getModuleStub).toHaveBeenCalled();
        expect(field.href).toEqual('#Quotes/12345');
    });

    it('should create normal sidecar if no bwc', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule', function() {
            return {isBwcEnabled: false};
        });
        var bwcBuildRouteStub = sinonSandbox.stub(app.bwc, 'buildRoute');
        var def = {
            link: true,
            route: {
                action: 'myaction'
            }
        };
        field = SugarTest.createField('base', 'text', 'base', 'list', def);
        field.model = new Backbone.Model({id: '12345', module: 'Quotes'});
        field._render();
        expect(getModuleStub).toHaveBeenCalled();
        expect(field.href).toEqual('#Quotes/12345/myaction');
        expect(bwcBuildRouteStub).not.toHaveBeenCalled();
    });
});
