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
        var module = 'Contacts';
        var context;

        app = SugarTest.app;

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
        sandbox.stub(app.utils, 'openEmailCreateDrawer');

        view.archiveEmail();

        expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledWith('create');
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].related).toBe(view.model);
        expect(app.utils.openEmailCreateDrawer.firstCall.args[1].to).toBe(view.model);
    });
});
