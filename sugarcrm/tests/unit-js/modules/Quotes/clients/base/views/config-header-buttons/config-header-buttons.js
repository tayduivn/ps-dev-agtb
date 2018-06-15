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
describe('Quotes.View.ConfigHeaderButtons', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.createView('base', 'Quotes', 'config-header-buttons', null, null, true);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('_getSaveConfigAttributes()', function() {
        beforeEach(function() {
            view.model.set({
                worksheet_columns: [{
                    name: 'aaa'
                }],
                worksheet_columns_related_fields: ['bbb']
            });
        });

        it('should add line_num to worksheet_columns', function() {
            var result = view._getSaveConfigAttributes();

            expect(result.worksheet_columns).toEqual([{
                name: 'line_num',
                label: null,
                widthClass: 'cell-xsmall',
                css_class: 'line_num tcenter',
                type: 'line-num',
                readonly: true
            }, {
                name: 'aaa'
            }]);
        });

        it('should add fields to worksheet_columns_related_fields', function() {
            var result = view._getSaveConfigAttributes();

            expect(result.worksheet_columns_related_fields).toEqual([
                'bbb',
                'description',
                'name',
                'product_template_id',
                'product_template_name'
            ]);
        });
    });
});
