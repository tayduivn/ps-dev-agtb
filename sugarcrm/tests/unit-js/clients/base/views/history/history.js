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
describe('Base.View.History', function() {
    var view;
    var app;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');
        var module = 'Contacts';
        var context;

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext();
        context.set({module: module});
        context.prepare();
        view = SugarTest.createView('base', module, 'history', null, context);

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        sandbox.restore();
    });

    it('should open the archive email drawer', function() {
        view.model.set('_acl', {});
        view.model.set('id', _.uniqueId());
        view.model.set('name', 'Randall Brothers');

        sandbox.stub(app.utils, 'openEmailCreateDrawer');

        view.archiveEmail();

        expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledWith('create');
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].related).toBe(view.model);
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to.module).toBe('EmailParticipants');
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to.get('_link')).toBe('to');
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to.get('parent')).toEqual({
            _acl: {},
            type: view.model.module,
            id: view.model.get('id'),
            name: view.model.get('name')
        });
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to.get('parent_type')).toBe(view.model.module);
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to.get('parent_id')).toBe(view.model.get('id'));
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to.get('parent_name')).toBe(view.model.get('name'));
    });
});
