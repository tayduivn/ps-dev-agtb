
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

describe('Currencies.Base.Views.Record', function() {
    var app;
    var view;
    var sinonSandbox;
    var model;

    afterEach(function() {
        sinonSandbox.restore();
    });

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        view = SugarTest.createView('base', 'Currencies', 'preview-header', null, null, true, null);
        model = app.data.createBean('Currencies');

        sinonSandbox.stub(view, '_super', function() {});
        spyOn(view, '_super');

    });

    describe('triggerEdit', function() {
        it('should not call super if base currency', function() {
            view.isBase = true;
            view.triggerEdit();

            expect(view._super).wasNotCalled();
        });

        it('should call super if not base currency', function() {
            view.isBase = false;
            view.triggerEdit();
            expect(view._super).toHaveBeenCalledWith('triggerEdit');
        });
    });

    describe('_delegateEvents', function() {
        beforeEach(function() {
            sinonSandbox.stub(app.events, 'on', function() {});
            sinonSandbox.stub(view, 'isBaseCurrency', function() {
                return false;
            });
        });

        it('should call super with _delegateEvents', function() {
            view._delegateEvents();
            expect(view._super).toHaveBeenCalledWith('_delegateEvents');
        });

        it('should call app.events.on with list:preview:decorate and false', function() {
            view._delegateEvents();
            expect(app.events.on).toHaveBeenCalledWith('list:preview:decorate');
        });
    });

    describe('isBaseCurrency', function() {
        it('should set isBase to false if currencyId is not -99', function() {
            model.set('id', '1');
            view.isBaseCurrency(model);
            expect(view.isBase).toBeFalsy();
        });

        it('should set isBase to true if currencyId is -99', function() {
            model.set('id', '-99');
            view.isBaseCurrency(model);
            expect(view.isBase).toBeTruthy();
        });
    });
});
