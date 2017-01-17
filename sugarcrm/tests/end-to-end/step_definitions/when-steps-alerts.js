var _ = require('lodash');

// turn of warnings like "Confusing use of '!'., W018"
/* jshint -W018 */

var myStepDefinitionsWrapper = function () {

    /**
     * Delete confirmation alert
     */
    this.When(/^I (Cancel|Confirm) confirmation alert$/, function (choice, callback) {
        var alert = seedbed.createComponent('AlertCmp', { type: 'warning' });
        alert.clickButton(choice.toLowerCase()).call(callback);
    }, true);

};

module.exports = myStepDefinitionsWrapper;
