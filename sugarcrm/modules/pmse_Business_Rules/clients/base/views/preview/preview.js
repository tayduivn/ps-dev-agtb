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
({
    extendsFrom: 'PreviewView',

    /**
     * Track the original model passed in from the worksheet, this is needed becuase of how the base preview works
     */
    originalModel: undefined,

    _renderField: function(field, $fieldEl) {
        if (field.type === 'hidden') {
            $fieldEl.parents('.row-fluid').eq(0).hide();
        }
        this._super("_renderField", arguments);
    }
})
