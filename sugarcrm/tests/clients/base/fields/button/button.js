describe("Base.Field.Button", function() {
    var app, field, Address;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","button", "button", "edit");
        app.view.Field.prototype._renderHtml = function() {};
        field._loadTemplate = function() {  this.template = function(){ return '<a class="btn" href="javascript:void(0);"></a>'}; };

    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field.model = null;
        field._loadTemplate = null;
        field = null;
        Address = null;
    });

    it('should bind custom event handlers', function() {
        field.def = {
            'events' : {
                'click' : 'function() { this.callback = "stuff excuted"; }',
                'blur' : 'function() { this.callback = "blur excuted"; }'
            }
        };
        field.render();
        field._renderHtml();
        expect(field.callback).toBeUndefined();
        field.$(".btn").trigger('click');
        expect(field.callback).toBe("stuff excuted");
        field.$(".btn").trigger('blur');
        expect(field.callback).not.toBe("stuff excuted");
        expect(field.callback).toBe("blur excuted");
        delete field.callback;
        field.$(".btn").trigger('undefined');
        expect(field.callback).not.toBe("stuff excuted");
        expect(field.callback).not.toBe("blur excuted");
        expect(field.callback).toBeUndefined();
    });
});