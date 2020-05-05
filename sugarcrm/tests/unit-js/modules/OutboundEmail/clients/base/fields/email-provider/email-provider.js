/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

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

    describe('handleOauthComplete()', function() {
        it('should return false if dataSource is wrong', function() {
            let data = {
                dataSource: 'fake source'
            };
            let e = {data: JSON.stringify(data)};
            field.value = 'google_oauth2';
            expect(field.handleOauthComplete(e)).toBeFalsy();
        });

        it('should set eapm_id in model', function() {
            let data = {
                dataSource: 'googleEmailRedirect',
                eapmId: 'fakeId',
                emailAddress: 'fakeEmail'
            };
            let e = {data: JSON.stringify(data)};
            field.value = 'google_oauth2';
            field.model.set('eapm_id', '');
            field.handleOauthComplete(e);
            expect(field.model.get('eapm_id')).toEqual('fakeId');
            expect(field.model.get('authorized_account')).toEqual('fakeEmail');
        });
    });

    describe('_checkAuth()', function() {
        it('should do nothing for non oauth2 providers', function() {
            let stub = sandbox.stub(field, 'render');
            field._checkAuth('exchange');
            expect(stub).not.toHaveBeenCalled();
        });

        it('should call auth api', function() {
            let urlStub = sandbox.stub(app.api, 'buildURL').returns('fakeUrl');
            let callStub = sandbox.stub(app.api,'call');
            field._checkAuth('google_oauth2');
            expect(callStub).toHaveBeenCalled();
        });

        it('should show warning, disable button and do not call auth api', function() {
            field.oauth2Types['google_oauth2'].auth_url = false;
            let callStub = sandbox.stub(app.api,'call');
            field._checkAuth('google_oauth2');
            expect(callStub).not.toHaveBeenCalled();
            expect(field.authWarning).toEqual(field.oauth2Types['google_oauth2'].auth_warning);
            expect(field.authButton).toEqual('disabled');
        });

        it('should enable button and show no warning', function() {
            field.oauth2Types['google_oauth2'].auth_url = 'fakeUrl';
            let callStub = sandbox.stub(app.api,'call');
            field._checkAuth('google_oauth2');
            expect(callStub).not.toHaveBeenCalled();
            expect(field.authWarning).toEqual('');
            expect(field.authButton).toEqual('enabled');
        });
    });
});
