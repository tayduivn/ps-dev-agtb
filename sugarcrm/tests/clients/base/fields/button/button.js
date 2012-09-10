describe("Base.Field.Button", function() {
    var app, field, Address;

    beforeEach(function() {
        app = SugarTest.app;
        app.view.Field.prototype._renderHtml = function() {};
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
        var def = {
            'events' : {
                'click .btn' : 'function() { this.callback = "stuff excuted"; }',
                'blur .btn' : 'function() { this.callback = "blur excuted"; }'
            }
        };

        field = SugarTest.createField("base","button", "button", "edit", def);
        field._loadTemplate = function() {  this.template = function(){ return '<a class="btn" href="javascript:void(0);"></a>'}; };

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