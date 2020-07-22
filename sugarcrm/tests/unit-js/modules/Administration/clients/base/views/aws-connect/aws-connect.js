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
describe('Administration.Views.AwsConnect', function() {
    var app;
    var view;
    var viewName = 'aws-connect';
    var moduleName = 'Administration';

    beforeEach(function() {
        app = SugarTest.app;
        var model = app.data.createBean(moduleName);
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName, moduleName);
        SugarTest.testMetadata.set();
        app.data.declareModels();

        var context = app.context.getContext();
        context.set('model', model);
        view = SugarTest.createView('base', moduleName, viewName, {}, context, true);
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        sinon.collection.restore();
    });

    describe('general behaviour', function() {
        it('copy the settings to the model', function() {
            var settings = {
                aws_connect_region: 'us-west-1',
            };
            expect(view.model.get('aws_connect_region')).toEqual(undefined);
            view.copySettingsToModel(settings);
            expect(view.model.get('aws_connect_region')).toEqual('us-west-1');
        });

        it('should init a call for loading aws settings', function() {
            var callStub = sinon.collection.stub(app.api, 'call');
            var buildStub = sinon.collection.stub(app.api, 'buildURL');
            view.loadSettings();
            expect(callStub).toHaveBeenCalled();
            expect(buildStub).toHaveBeenCalledWith('Administration', 'aws');
        });

        it('should update app config with the saved values', function() {
            var settings = {
                aws_connect_region: 'us-west-1',
                aws_connect_instance_name: 'my_connect_instance_name'
            };
            var toggleStub = sinon.collection.stub(view, 'toggleHeaderButton');
            view.saveSuccessHandler(settings);
            expect(toggleStub).toHaveBeenCalled();
            expect(app.config.awsConnectRegion).toEqual('us-west-1');
            expect(app.config.awsConnectInstanceName).toEqual('my_connect_instance_name');
        });
    });
});
