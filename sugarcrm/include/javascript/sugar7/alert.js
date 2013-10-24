/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
(function(app) {

    /**
     * This file handles the alerts for the sidecar sync events
     *
     * Sidecar provides 5 events on which we will display/dismiss alerts:
     *
     *  - app:sync indicates the beginning of app.sync()
     *  - app:sync:complete indicates app.sync() has finished without errors
     *  - app:sync:error indicates app.sync() has finished with errors
     *  - data:sync:start indicates we are synchronizing a Bean or BeanCollection (fetch/save/destroy)
     *  - data:sync:complete indicates the Bean or BeanCollection sync has finished successfully or not
     */

    /**
     * On 'app:sync' we display a simple 'LBL_LOADING' process alert
     */
    app.events.on('app:sync', function() {
        app.alert.show('app:sync', {level: 'process', title: app.lang.getAppString('LBL_LOADING')});
    });

    /**
     * On 'app:sync:complete' and 'app:sync:error' we dismiss the alert
     */
    app.events.on('app:sync:complete', function() {
        app.alert.dismiss('app:sync');
    });
    app.events.on('app:sync:error', function() {
        app.alert.dismiss('app:sync');
    });


    /**
     * Override Context.loadData to attach showAlerts flag if it's the primary context.
     * While loading data of the primary context  we will display a processing message.
     *
     * @param options
     */
    var _contextProto = _.clone(app.Context.prototype);
    app.Context.prototype.loadData = function(options) {
        if (!this.parent) {
            options = options || {};
            options.showAlerts = true;
        }
        _contextProto.loadData.call(this, options);
    };

    /**
     * By default, on 'data:sync:start' we DON'T display a process alert
     *
     * You can pass options.showAlerts = true to your requests to enable the alert messages.
     *
     *      var bean = app.data.createBean('Accounts')
     *      bean.fetch({
     *          showAlerts: true
     *      });
     *
     * You can also override the alert options (including the title and messages) by passing an object 'showAlerts'
     * such as:
     *
     *      var bean = app.data.createBean('Accounts')
     *      bean.save(null, {
     *          showAlerts: {
     *              'process' : {
     *                  'level' : 'warning',
     *                  'title' : 'Saving...',
     *                  'messages' : 'This request takes a few minutes'
     *              },
     *              'success' : {
     *                  'messages' : 'Enjoy the data. '
     *              }
     *          }
     *      });
     *
     *  You may want to display only the success alert
     *
     *      bean.save(null, {
     *          showAlerts: {
     *              'process' : false,
     *              'success' : {
     *                  'read' : {
     *                      'messages' : 'Enjoy the data. '
     *                  }
     *              }
     *          }
     *      });
     */
    var numActiveProcessAlerts = 0;
    app.events.on('data:sync:start', function(method, model, options) {

        options = options || {};

        // By default we don't display the alert
        if (!options.showAlerts) {
            return;
        }

        // The user can have disabled only the process alert
        if (options.showAlerts.process === false) {
            return;
        }

        // From here we are sure we want to show the process alert
        var alertOpts = {
            level: 'process'
        };

        // Pull labels for each method
        if (method === 'read') {
            alertOpts.title = app.lang.getAppString('LBL_LOADING');
        }
        else if (method === 'delete') {
            // options.relate means we are breaking a relationship between two records, not actually deleting a record
            alertOpts.title = options.relate ?
                app.lang.getAppString('LBL_UNLINKING') : app.lang.getAppString('LBL_DELETING');
        }
        else {
            alertOpts.title = app.lang.getAppString('LBL_SAVING');
        }

        // Check for an alert options object attach to options that would override
        if (_.isObject(options.showAlerts.process)) {
            _.extend(alertOpts, options.showAlerts.process);
        }

        // Increase the counter so we know have many process alerts are currently being displayed
        numActiveProcessAlerts++;
        app.alert.show('data:sync:process', alertOpts);
    });

    // Not to be confused with the event fired for data:sync:complete.
    var syncCompleteHandler = function(type, messages, method, model, options) {
        // Preconstruct the alert options.
        var alertOpts = {
            level: type,
            messages: messages,
            autoClose: true
        };
        options = options || {};

        // By default we don't display the alert
        if (!options.showAlerts) return;

        // As we display alerts we have have to check if there is a process alert to dismiss prior to display the success one
        // (as many requests can be fired at the same time we make sure not to dismiss another process alert!)
        if (options.showAlerts.process !== false) {
            // Decrease the number of alerts to dismiss
            numActiveProcessAlerts--;
            // Dismiss only if it's the last one
            if (numActiveProcessAlerts < 1) {
                numActiveProcessAlerts = 0;
                app.alert.dismiss('data:sync:process');
            }
        }

        // Error module will display proper message
        if (method === 'read') return;

        // The user can have disabled only this particular type of alert.
        if (options.showAlerts[type] === false) return;

        // Check for an alert options object attach to options
        if (_.isObject(options.showAlerts[type])) {
            _.extend(alertOpts, options.showAlerts[type]);
        }

        app.alert.show('data:sync:' + type, alertOpts);
    };

    app.events.on('data:sync:success', function(method, model, options) {
        var messages;

        if (method === 'delete') {
            // options.relate means we are breaking a relationship between two records, not actually deleting a record
            messages = options.relate ? 'LBL_UNLINKED' : 'LBL_DELETED';
        }
        else {
            messages = 'LBL_SAVED';
        }

        syncCompleteHandler('success', messages, method, model, options);
    });

    app.events.on('data:sync:error', function(method, model, options, error) {
        //412 errors must be handled by re-sending the update/save after the app finishes the sync.
        if (!error || error.status != 412) {
            syncCompleteHandler('error', 'ERR_GENERIC_SERVER_ERROR', method, model, options);
        } else {
            //Hide the saving dialog so that only the "loading" screen is visible
            app.alert.dismiss("data:sync:process");
        }
    });

})(SUGAR.App);
