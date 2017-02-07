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
/*
Represents header view PageObject
 */

var Cukes = require('@sugarcrm/seedbed'),
    BaseView = Cukes.BaseView,
    utils = Cukes.Utils;

/**
 * @class SugarCukes.HeaderView
 * @extends Cukes.BaseView
 */
class HeaderView extends BaseView{

    constructor(options) {
        super(options);

        this.selectors = {
            $: ".headerpane",
                buttons: {
                'create'  : 'a[name="create_button"]',
                    'cancel'  : 'a[name="cancel_button"]',
                    'save'    : 'a[name="save_button"]',
                    'edit'    : 'a[name="edit_button"]',
                    'delete'  : 'a[name="delete_button"]',
                    'actions' : '.actions:not([style*="display: none"]) a.btn.dropdown-toggle'
            },

            title: {
                'old' : 'h1 [data-name="title"] span.list-headerpane',
                    'new' : 'h1 [data-name="title"] span.list-headerpane div'
            }
        };
    }

    /**
     * Get Header Panel Title text
     *
     * @param callback
     */
    getTitleText (callback) {

        // Temp fix for Header selectors for 7.6
        var selector = utils.compareVersion(seedbed.serverVersion, '7.7.0.0', '<') ?
            this.$('title.old') :
            this.$('title.new');

        seedbed.client.getText(selector).then((value) => {
            callback(null, value);
        });
    }
}

module.exports = HeaderView;
