describe('KBSContents.Field.Date', function() {
    var app, field,
        module = 'KBSContents',
        fieldName = 'date',
        fieldType = 'date',
        model, sinonSandbox;

    beforeEach(function() {
        sinonSandbox = sinon.sandbox.create();
        Handlebars.templates = {};
        SugarTest.loadHandlebarsTemplate(fieldType, 'field', 'base', 'edit', module);
        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', {}, module, model, null, true);
    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
        sinonSandbox.restore();
    });

    it('Expiration date cannot be lower than publishing.', function() {
        var validateSpy = sinonSandbox.spy(function(mod, field, errors) {
        });
        field.model.set('status', 'draft');
        field.model.set('exp_date', '2010-10-10');
        field.model.set('active_date', '2011-10-10');

        field._doValidateExpDateField([], [], validateSpy);

        expect(validateSpy.args[0][2].exp_date.expDateLow).toBeTruthy();
    });

    it('Approved requires publishing date and the field on view.', function() {
        var validateSpy = sinonSandbox.spy(function(mod, field, errors) {
        });
        sinonSandbox.stub(field.view, 'getField', function(name) {
            if (name == 'active_date') {
                return {name: 'active_date'};
            }
        });

        field.model.set('status', 'approved');
        field.model.set('active_date', null);

        field._doValidateActiveDateField([], [], validateSpy);

        expect(validateSpy.args[0][2].active_date.activeDateApproveRequired).toBeTruthy();
    });

    it('Approved requires publishing date and the field not on view.', function() {
        var validateSpy = sinonSandbox.spy(function(mod, field, errors) {
        });
        sinonSandbox.stub(field.view, 'getField', function(name) {
            if (name == 'active_date') {
                return undefined;
            }
        });
        field.model.set('status', 'approved');
        field.model.set('active_date', null);

        field._doValidateActiveDateField([], [], validateSpy);

        // The validation decorator should be on the status field.
        expect(validateSpy.args[0][2].status.activeDateApproveRequired).toBeTruthy();
    });

    it('Expiration changes own date to current.', function() {
        sinonSandbox.stub(field.model, 'changedAttributes', function() {
            return {status: 'published'};
        });
        field.model.set('status', 'expired');
        field._validationComplete(true);

        expect(field.model.get('exp_date')).toEqual(app.date().formatServer(true));
    });

    it('Publishing changes own date to current.', function() {
        sinonSandbox.stub(field.model, 'changedAttributes', function() {
            return {status: 'approved'};
        });
        field.model.set('status', 'published');
        field._validationComplete(true);

        expect(field.model.get('active_date')).toEqual(app.date().formatServer(true));
    });

    it('Switching from publishing to publishing should not change own date.', function() {
        sinonSandbox.stub(field.model, 'changedAttributes', function() {
            return {status: 'published'};
        });
        var expectedDate = '2000-10-10';
        field.model.set('active_date', '2000-10-10');
        field.model.set('status', 'published-in');
        field._validationComplete(true);

        expect(field.model.get('active_date')).toEqual(expectedDate);
    });

});
