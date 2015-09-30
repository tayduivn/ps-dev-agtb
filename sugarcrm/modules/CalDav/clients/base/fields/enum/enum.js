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
extendsFrom: 'EnumField',

loadEnumOptions: function(fetch, callback) {
    this._super('loadEnumOptions', [fetch, callback]);

    var field_options = this.model.get(this.name+'_options');

    if (field_options) {
        switch(this.name) {
            case 'caldav_module':
                var temp = new Object();
                field_options.forEach(function(item, i, arr) {
                    temp[item]=item;
                });
                this.items = temp;
                break;
            case 'caldav_interval':
                this.items = field_options;
                break;
        }
    }
}
})
