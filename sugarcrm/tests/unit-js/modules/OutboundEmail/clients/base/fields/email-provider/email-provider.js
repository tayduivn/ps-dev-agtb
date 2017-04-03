describe('OutboundEmail.BaseEmailProviderField', function() {
    var app;
    var context;
    var field;
    var model;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.declareData('base', 'OutboundEmail', true, false);
        SugarTest.loadHandlebarsTemplate('email-provider', 'field', 'base', 'detail', 'OutboundEmail');
        SugarTest.loadHandlebarsTemplate('email-provider', 'field', 'base', 'edit', 'OutboundEmail');
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'field', 'radioenum');
        SugarTest.loadComponent('base', 'field', 'email-provider', 'OutboundEmail');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'OutboundEmail'});
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        sandbox.restore();

        field.dispose();
        app.cache.cutAll();
        app.view.reset();

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('rendering in disabled mode', function() {
        it('should use the detail template', function() {
            field = SugarTest.createField({
                name: 'mail_smtptype',
                type: 'email-provider',
                viewName: 'edit',
                fieldDef: {
                    name: 'mail_smtptype',
                    type: 'enum',
                    options: 'mail_smtptype_options',
                    default: 'other'
                },
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });

            field.render();
            expect(field.tplName).toBe('edit');
            expect(field.template).toBe(Handlebars.templates['f.email-provider.OutboundEmail.edit']);

            sandbox.spy(field, '_getFallbackTemplate');
            field.setDisabled();
            expect(field.tplName).toBe('disabled');
            expect(field.template).toBe(Handlebars.templates['f.email-provider.OutboundEmail.detail']);
            expect(field._getFallbackTemplate.alwaysReturned('detail')).toBe(true);
        });
    });
});
