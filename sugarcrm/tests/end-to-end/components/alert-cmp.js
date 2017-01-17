/*
 Alert.
 */

var Cukes = require('@sugarcrm/seedbed'),
    BaseView = Cukes.BaseView;

/**
 * @class SugarCukes.AlertCmp
 * @extends Cukes.BaseView
 */
class AlertCmp extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = {
            container : "#alerts .alert-{{alertType}}",
                closeIcon : ".icon-remove",
                message   : ".message",
                buttons : {
                'confirm' : 'a.alert-btn-confirm',
                    'cancel'  : 'a.alert-btn-cancel'
            }
        };

        this.alertType = options.type; //{load | success | ... }
        //each CUD alert should have method name {create | update | delete}
        this.method = options.method;
    }

    /**
     * Close Alert
     */
    close() {
        return seedbed.client.waitForVisibleAndClick(this.$('closeIcon'));
    }

    /**
     * Get Alert Message
     *
     * @param callback
     * @returns {*}
     */
    getMessage(callback) {
        return seedbed.client.getText(this.getAlertMessageSelector()).then(result => {
            callback(result);
        }).catch(err => {
            callback(err);
        });
    }

    /**
     * Get Alert Selector
     * @returns {*|String}
     */
    getAlertSelector () {
        return this.$('container', {alertType: this.alertType});
    }

    /**
     * Get Alert Message Selector
     * @returns {*}
     */
    getAlertMessageSelector () {
        return this.getAlertSelector() + this.$('message');
    }

    /**
     * Wait for Alert
     *
     * @param callback
     */
    waitForInvisible (callback) {
        if (callback) {
            seedbed.client.waitFor(this.getAlertSelector(), seedbed.config.waitForUnexepectedAlert).then(() => {
                callback();
            });
        } else {
            return seedbed.client.waitFor(this.getAlertSelector(), seedbed.config.waitForUnexepectedAlert);
        }
    }

    /**
     * Wait for Alert shown
     *
     * @param callback
     */
    waitForVisible(callback) {
        if (callback) {
            seedbed.client.waitFor(this.getAlertSelector()).then(() => {
                callback();
            });
        } else {
            return seedbed.client.waitFor(this.getAlertSelector());
        }
    }

    /**
     * Wait for Alert disappear
     *
     * @param callback
     * @returns {*}
     */
    waitForDetach(callback) {
        if (callback) {
            seedbed.client.waitForDetach(this.getAlertSelector()).then(() => {
                callback();
            });
        } else {
            return seedbed.client.waitForDetach(this.getAlertSelector());
        }
    }
};

module.exports = AlertCmp;
