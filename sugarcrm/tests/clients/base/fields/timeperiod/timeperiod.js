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
describe("Base.Field.Timeperiod", function() {
    var app, field, template,
        module = 'Bugs',
        fieldName = 'foo',
        fieldDef = {
            events: {
                'click input.selection': 'toggleSelect'
            }
        };

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        template = SugarTest.loadHandlebarsTemplate('timeperiod', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('timeperiod', 'field', 'base', 'tooltip-default');
        SugarTest.testMetadata.set();
        field = SugarTest.createField('base', fieldName, 'timeperiod', 'detail', fieldDef, module);
    });

    afterEach(function() {
        app.cache.cutAll();
        sinon.collection.restore();
        field.dispose();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    using('values', ['ltr', 'rtl'], function(value) {
        beforeEach(function() {
            app.lang.direction = value;
        });

        describe('initialize', function() {
            it('should set the proper language direction label', function () {
                var lbl = 'LBL_DROPDOWN_TOOLTIP' + (value == 'rtl' ? '_RTL' : '');
                expect(field.tooltipKey).toBe(lbl);
            });
        });
    });
});
