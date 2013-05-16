describe("Base.Field.Follow", function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        var model = new Backbone.Model({
                id: '1234567890'
            });
        field = SugarTest.createField("base", "follow", "follow", "edit", null, null, model);

    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field.model = null;
        field._loadTemplate = null;
        field = null;
    });
    describe("Label", function() {
        it('should assign the label as LBL_FOLLOW when the following value is empty', function() {
            expect(field.label).toBe('LBL_FOLLOW');
        });

        it('should assign the label as LBL_UNFOLLOW once following set as true', function() {
            field.model.set("following", true);
            expect(field.label).toBe('LBL_UNFOLLOW');
        });

        it('should assign the label back to LBL_FOLLOW once following set as false', function() {
            field.model.set("following", false);
            expect(field.label).toBe('LBL_FOLLOW');
        });

        it('should trigger "show" listener once label is updated', function() {
            var showStub = sinon.stub(field, 'trigger');
            field.model.set("following", true);
            expect(showStub).toHaveBeenCalled();
            expect(showStub).toHaveBeenCalledWith('show');
            showStub.restore();
        });
    });

    describe("Label for detail view", function() {
        beforeEach(function() {
            field.setMode("detail");
        });

        it('should assign the label as LBL_FOLLOW when the following value is empty', function() {
            expect(field.label).toBe('LBL_FOLLOW');
        });

        it('should assign the label as LBL_FOLLOWING once following set as true', function() {
            field.model.set("following", true);
            expect(field.label).toBe('LBL_FOLLOWING');
        });

        it('should assign the label back to LBL_FOLLOW once following set as false', function() {
            field.model.set("following", false);
            expect(field.label).toBe('LBL_FOLLOW');
        });

    });
});
