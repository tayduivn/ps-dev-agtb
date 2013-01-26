describe("Base.Field.Listeditable", function() {
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

    it('should have custom templates for list-edit and list-disabled', function() {
        var def = {
            'events' : {
                'click .btn' : 'function() { this.callback = "stuff excuted"; }',
                'blur .btn' : 'function() { this.callback = "blur excuted"; }'
            }
        };

        field = SugarTest.createField("base","listeditable", "listeditable", "edit", def);

        expect(field.view.action).not.toBe('list');
        field._loadTemplate();
        expect(field.tplName).toBe('edit');

        field.view.action = 'list';
        field._loadTemplate();
        expect(field.tplName).toBe('list-edit');

        field.setDisabled(true);
        expect(field.tplName).toBe('list-disabled');

        field.view.action = 'edit';
        field.setDisabled(false);
        expect(field.tplName).toBe('edit');
    });

});
