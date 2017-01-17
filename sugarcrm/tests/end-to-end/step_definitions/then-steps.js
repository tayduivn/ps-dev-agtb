var _ = require('lodash'),
    listHelper = require('../support/list-helper.js');

// turn of warnings like "Confusing use of '!'., W018"
/* jshint -W018 */

var myStepDefinitionsWrapper = function () {

    /**
     * Check whether the cached view is visible
     * Note: for *Edit and *Detail views the opened url is checked to have an id form the cached record
     *
     * @example "I should see #AccountsList view"
     */
    this.Then(/^I should (not )?see (#\S+) view$/,
        function (not, layout, callback) {
            layout.isVisibleView(function (value) {
                if (_.isBoolean(value)) {
                    !not === value ? callback() : callback.fail('Expected ' + (not || '') + 'to see "' + layout.$className + '" view(layout)');
                } else {
                    callback.fail('Error: ' + value);
                }
            });
        });

};

module.exports = myStepDefinitionsWrapper;
