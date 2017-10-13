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
describe('includes.javascript.pmse.ui.expression_builder2', function() {
    describe('init', function() {
        it('should persist the name attribute in the local variable', function() {
            var params = {
                name: 'evn_criteria'
            };
            var control = new ExpressionControl(params);
            expect(control._name).toEqual(params.name);
        });
    });
});
