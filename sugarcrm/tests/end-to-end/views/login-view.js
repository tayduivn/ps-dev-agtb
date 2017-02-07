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
Represents Login page PageObject.
 */

var Cukes = require('@sugarcrm/seedbed'),
    BaseView = Cukes.BaseView;

/**
 * @class SugarCukes.LoginView
 * @extends Cukes.BaseView
 */
class LoginView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = {

            "username": 'input[name=username]',
                "password": 'input[name=password]',
                buttons: {
                "login": "a[name=login_button]"
            }
        };

    }

    login(username, password, callback) {

        var chain = seedbed.client
            .setValue(this.$('username'), username)
            .setValue(this.$('password'), password);

        return chain.click(this.$('buttons.login'))
            .call(callback);
    }
}

module.exports = LoginView;
