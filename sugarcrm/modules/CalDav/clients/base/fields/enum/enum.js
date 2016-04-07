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
({
extendsFrom: 'EnumField',

loadEnumOptions: function(fetch, callback) {
    this._super('loadEnumOptions', [fetch, callback]);

    var field_options = this.model.get(this.name + '_options');

    if (field_options) {
        this.items = field_options;
    }
}
})
