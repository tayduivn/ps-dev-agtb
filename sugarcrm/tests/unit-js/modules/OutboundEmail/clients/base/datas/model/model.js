describe('Data.Base.OutboundEmailBean', function() {
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.declareData('base', 'OutboundEmail', true, false);
        app.data.declareModels();

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        sandbox.restore();
        SugarTest.testMetadata.dispose();
    });

    it('should default `name`, `email_address`, and `email_address_id`', function() {
        var model;
        var name = 'Jack Edwards';
        var primary = 'foo@bar.com';
        var email = [{
            'email_address': primary,
            'email_address_id': _.uniqueId(),
            'opt_out': false,
            'invalid_email': false,
            'primary_address': true,
            'reply_to_address': true
        }];
        var stub = sandbox.stub(app.user, 'get');

        stub.withArgs('full_name').returns(name);
        stub.withArgs('email').returns(email);
        sandbox.stub(app.utils, 'getPrimaryEmailAddress').returns(primary);

        model = app.data.createBean('OutboundEmail');

        // Defaults are defined.
        expect(model.getDefault('name')).toBe(name);
        expect(model.getDefault('email_address')).toBe(primary);
        expect(model.getDefault('email_address_id')).toBe(email[0].email_address_id);

        // Defaults are applied.
        expect(model.get('name')).toBe(name);
        expect(model.get('email_address')).toBe(primary);
        expect(model.get('email_address_id')).toBe(email[0].email_address_id);
    });
});
