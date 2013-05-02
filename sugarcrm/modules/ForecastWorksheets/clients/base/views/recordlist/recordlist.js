/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    extendsFrom: 'RecordlistView',

    selectedUser: {},

    initialize: function(options) {
        this.plugins.push('cte-tabbing');
        app.view.views.RecordlistView.prototype.initialize.call(this, options);
        this.selectedUser = this.context.get('selectedUser') || this.context.parent.get('selectedUser') || app.user.toJSON();
        this.context.set('skipFetch', true); // skip the initial fetch, this will be handled by the changing of the selectedUser
        this.collection.sync = _.bind(this.sync, this);
    },

    bindDataChange: function() {
        // these are handlers that we only want to run when the parent module is forecasts
        if (!_.isUndefined(this.context.parent) && !_.isUndefined(this.context.parent.get('model'))) {
            if (this.context.parent.get('model').module == 'Forecasts') {
                this.before('render', function() {
                    // set the defaults to make it act like a manager so it doesn't actually render till the selected
                    // user is updated
                    var showOpps = (_.isUndefined(this.selectedUser.showOpps)) ? false : this.selectedUser.showOpps,
                        isManager = (_.isUndefined(this.selectedUser.isManager)) ? true : this.selectedUser.isManager;

                    if (!(showOpps || !isManager) && !this.layout.$el.hasClass('hide')) {
                        console.log('beforeRender Hide');
                        this.layout.hide();
                    } else if ((showOpps || !isManager) && this.layout.$el.hasClass('hide')) {
                        console.log('beforeRender Show');
                        this.layout.show();
                    }

                    console.log('Rep beforeRender will return ', (showOpps || !isManager));

                    return (showOpps || !isManager);
                });
                this.on('render', function() {
                    var user = this.context.parent.get('selectedUser') || app.user.toJSON()
                    if (user.showOpps || !user.isManager) {
                        if (this.layout.$el.hasClass('hide')) {
                            console.log('Rep Show', user);
                            this.layout.show();
                        }
                    } else {
                        if (!this.layout.$el.hasClass('hide')) {
                            console.log('Rep Hide', user);
                            this.layout.hide();
                        }
                    }
                }, this);

                this.context.parent.on('change:selectedUser', function(model, changed) {
                    var doFetch = false;
                    console.log('Rep SelectedUser Change');
                    if (this.selectedUser.id != changed.id) {
                        doFetch = true;
                    }
                    // if we are already not going to fetch, check to see if the new user is showingOpps or is not
                    // a manager, then we want to fetch
                    if (!doFetch && (changed.showOpps || !changed.isManager)) {
                        doFetch = true;
                    }
                    this.selectedUser = changed;

                    if (doFetch) {
                        this.collection.fetch();
                    } else {
                        if ((!this.selectedUser.showOpps && this.selectedUser.isManager) && !this.layout.$el.hasClass('hide')) {
                            // we need to hide
                            console.log('rep fetch hide');
                            this.layout.hide();
                        }
                    }
                }, this);
            }
        }

        app.view.views.RecordlistView.prototype.bindDataChange.call(this);
    },

    sync: function(method, model, options) {
        var callbacks,
            url;

        options = options || {};
        options.params = options.params || {};

        if (!_.isUndefined(this.selectedUser.id)) {
            options.params.user_id = this.selectedUser.id;
        }

        options.limit = 1000;
        options = app.data.parseOptionsForSync(method, model, options);

        // custom success handler
        options.success = _.bind(function(model, data, options) {
            this.collection.reset(data);
        }, this);

        callbacks = app.data.getSyncCallbacks(method, model, options);
        this.trigger("data:sync:start", method, model, options);

        url = app.api.buildURL("ForecastWorksheets", null, null, options.params);
        app.api.call("read", url, null, callbacks);
    },

    /**
     * We have to overwrite this method completely, since there is currently no way to completely disable
     * a field from being displayed
     *
     * @returns {{default: Array, available: Array, visible: Array, options: Array}}
     */
    parseFields: function() {
        var catalog = {
            'default': [], //Fields visible by default
            'available': [], //Fields hidden by default
            'visible': [], //Fields user wants to see,
            'options': []
        };
        // TODO: load field prefs and store names in this._fields.available.visible
        // no prefs so use viewMeta as default and assign hidden fields
        _.each(this.meta.panels, function(panel) {
            _.each(panel.fields, function(fieldMeta, i) {
                if (app.utils.getColumnVisFromKeyMap(fieldMeta.name, 'forecastsWorksheetManager')) {
                    if (fieldMeta['default'] === false) {
                        catalog.available.push(fieldMeta);
                    } else {
                        catalog['default'].push(fieldMeta);
                        catalog.visible.push(fieldMeta);
                    }
                    catalog.options.push(_.extend({
                        selected: (fieldMeta['default'] !== false)
                    }, fieldMeta));
                }
            }, this);
        }, this);
        return catalog;
    }
})
