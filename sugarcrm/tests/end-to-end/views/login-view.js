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
