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
describe('Archive Email View', function() {
    var view;
    var sandbox;

    beforeEach(function() {
        view = SugarTest.createView('base', 'Emails', 'archive-email', null, null, true);

        sandbox = sinon.sandbox.create();
        sandbox.stub(view, 'setMainButtonsDisabled');
    });

    afterEach(function() {
        sandbox.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        SugarTest.app.cache.cutAll();
        SugarTest.app.view.reset();
        Handlebars.templates = {};
    });

    describe('archive', function() {
        it('should save the archive email if the validation passes', function() {
            view.save = sandbox.stub();

            sandbox.stub(view.model, 'doValidate', function(fields, callback) {
                callback(true);
            });
            view.archive();

            expect(view.save.calledOnce).toBe(true);
        });

        it('should not save the archive email if the validation fails', function() {
            view.save = sandbox.stub();

            sandbox.stub(view.model, 'doValidate', function(fields, callback) {
                callback(false);
            });
            view.archive();

            expect(view.save.calledOnce).toBe(false);
        });

        it('should first disable the archive button and then enable it back when validation fails', function() {
            sinon.collection.stub(view.model, 'doValidate', function(fields, callback) {
                callback(false);
            });

            view.archive();

            expect(view.setMainButtonsDisabled.calledTwice).toBe(true);
            expect(view.setMainButtonsDisabled.getCall(0).args[0]).toBe(true);
            expect(view.setMainButtonsDisabled.getCall(1).args[0]).toBe(false);
        });
    });
});
