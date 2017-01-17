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
