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
var Cukes = require('@sugarcrm/seedbed'),
    BaseLayout = Cukes.BaseLayout;

/**
 * Represents Login page layout.
 *
 * @class SugarCukes.LoginLayout
 * @extends Cukes.BaseLayout
 */
class LoginLayout extends BaseLayout{

    constructor(options) {

        super(options);

        this.type = 'login';

        this.addView('LoginView', 'LoginView', {module: 'Login',default: true});

        this.selectors = {
            $: '#sugarcrm .thumbnail.login'
        };

    }
}

module.exports = LoginLayout;
