(function(app) {

    /**
     * This file handles the alerts for the sidecar sync events
     */

    /**
     * On 'app:sync' we display a simple 'LBL_LOADING' process alert
     */
    app.events.on('app:sync', function() {
        app.alert.show('app:sync', {level: 'process', title: app.lang.getAppString('LBL_LOADING')});
    });

    /**
     * On 'app:sync:complete' and 'app:sync:error'
     * we dismiss the alert
     */
    app.events.on('app:sync:complete', function() {
        app.alert.dismiss('app:sync');
    });
    app.events.on('app:sync:error', function() {
        app.alert.dismiss('app:sync');
    });


    /**
     * Override Context.loadData to attach _hideAlertsOn {Array} on the Bean or BeanCollection.
     *
     * Sidecar triggers global 'data:sync:start' in data-manager to indicate when
     * the Bean or BeanCollection begins. We will display an alert during each sync process
     *
     * If you want to prevent this behavior.
     * the implementation above allows you to pass a context attribute
     * 'hideAlertsOn' and an {Array} of methods or a {String} (if only one method)
     *
     * Meta examples:
     *  1/ You have a custom implementation for sync alerts in your view and want to block the default
     *     behavior.
     *     Pass it the following context attribute:
     *
     *      {
     *          'view' : 'viewName',
     *          'context' : {
     *              'hideAlertsOn' : ['create', 'read', 'update', 'delete']
     *          }
     *      }
     *
     *  2/ You have one main view and several subviews and you want to display the 'Loading' alert only
     *     for the main view.
     *     Give the subviews the following context attribute:
     *
     *      {
     *          'view' : 'viewName',
     *          'context' : {
     *              'hideAlertsOn' : 'read'
     *          }
     *      }
     *
     * @param options
     */
    var _contextProto = _.clone(app.Context.prototype);
    app.Context.prototype.loadData = function(options) {
        var objectToFetch,
            modelId = this.get('modelId');

        objectToFetch = modelId ? this.get('model') : this.get('collection');
        if (this.has('hideAlertsOn')) {
            var hiddenMethods = this.get('hideAlertsOn');
            objectToFetch._hideAlertsOn = _.isArray(hiddenMethods) ? hiddenMethods : [hiddenMethods];
        }
        _contextProto.loadData.call(this, options);
    };

    /**
     * On 'data:sync:start' we display per method process alert
     *
     * As mentioned below you can pass a context attribute _hideAlertsOn if you don't want to
     * display an alert for a specific view.
     *
     *      var bean = app.data.createBean('Accounts')
     *      bean._hideAlertsOn = ['read'];
     *      bean.fetch();
     *
     * You can override the alert options (including the title and messages)
     * by passing an object 'alerts' to the Backbone options object such as:
     *
     *      var bean = app.data.createBean('Accounts')
     *      bean._hideAlertsOn = ['read'];
     *      bean.save(null, {
     *          alerts: {
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
     *      You can also not be ok to display an alert on a specific request without attaching
     *      _hideAlertsOn to the Bean or BeanCollection so it doesn't affect other requests
     *
     *      bean.save(null, {
     *          alerts: false
     *      });
     *
     *      Or if you want to display only the success alert
     *
     *         bean.save(null, {
     *          alerts: {
     *              'process' : false,
     *              'success' : {
     *                  'read' : {
     *                      'messages' : 'Enjoy the data. '
     *                  }
     *              }
     *          }
     *      });
     *
     *      This works for fetch, save and destroy.
     */
    var _methods = ['create', 'read', 'update', 'delete'];
    var nbProcessAlerts = 0;
    app.events.on('data:sync:start', function(method, model, options) {

        // First, check if we don't want to show the alert
        if (_.isArray(model._hideAlertsOn) && _.indexOf(model._hideAlertsOn, method) !== -1) {
            return;
        }

        options = options || {};
        if (options.alerts === false || (_.isObject(options.alerts) && options.alerts.process === false)) {
            return;
        }

        // From here we are sure we want to show the process alert
        var alert = {},
            alertOpts = {
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
        if (_.isObject(options.alerts) && _.isObject(options.alerts.process)) {
            alert = options.alerts.process;
        }
        alertOpts = _.extend({}, alertOpts, alert);

        // Increase the counter so we know have many process alerts are currently being displayed
        nbProcessAlerts++;
        app.alert.show('data:sync:process', alertOpts);
    });

    app.events.on('data:sync:end', function(method, model, options, error) {

        // First, check if there is a process alert to dismiss
        // (as many requests can be fired at the same time we make sure not to dismiss another alert!)
        if (_.isUndefined(options.alerts) || (_.isObject(options.alerts) && options.alerts.process !== false)) {
            // Decrease the number of alerts to dismiss
            nbProcessAlerts--;
            // Dismiss only if it's the last one
            if (nbProcessAlerts < 1) {
                nbProcessAlerts = 0;
                app.alert.dismiss('data:sync:process');
            }
        }

        // Error module will display proper message
        if (error || method === 'read') return;

        if (_.isArray(model._hideAlertsOn) && _.indexOf(model._hideAlertsOn, method) !== -1) {
            //Don't show any alert for this method
            return;
        }

        options = options || {};
        if (options.alerts === false || (_.isObject(options.alerts) && options.alerts.success === false)) {
            return;
        }

        // From here we are sure we want to show the success alert
        var alert = {},
            alertOpts = {
                level: 'success',
                autoClose: true
            };
        // Check for an alert options object attach to options
        if (_.isObject(options.alerts) && _.isObject(options.alerts.success)) {
            alert = options.alerts.success;
        }

        if (method === 'delete') {
            // options.relate means we are breaking a relationship between two records, not actually deleting a record
            alertOpts.messages = options.relate ? 'LBL_UNLINKED' : 'LBL_DELETED';
        }
        else {
            alertOpts.messages = 'LBL_SAVED';
        }
        alertOpts = _.extend({}, alertOpts, alert);
        app.alert.show('data:sync:success', alertOpts);
    });

})(SUGAR.App);
