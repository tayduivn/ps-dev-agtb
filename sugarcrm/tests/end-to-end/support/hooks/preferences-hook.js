var async = require('async');

module.exports = function() {
    this.registerHandler('BeforeFeatures', function (event, callback) {

        return seedbed.api.login({}).then(() => {
            return seedbed.api.updatePreferences({
                preferences: seedbed.config.users.default.defaultPreferences,
            });
        }).then(() => {
            callback();
        });

    });
};
