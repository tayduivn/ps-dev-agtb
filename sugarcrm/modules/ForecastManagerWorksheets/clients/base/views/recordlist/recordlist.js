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

    totals: {},

    defaultValues: {
        quota: 0,
        best_case: 0,
        best_case_adjusted: 0,
        likely_case: 0,
        likely_case_adjusted: 0,
        worst_case: 0,
        worst_case_adjusted: 0
    },

    initialize: function(options) {
        this.plugins.push('cte-tabbing');
        app.view.views.RecordlistView.prototype.initialize.call(this, options);
        this.selectedUser = this.context.get('selectedUser') || this.context.parent.get('selectedUser') || app.user.toJSON();
        this.context.set('skipFetch', true);    // skip the initial fetch, this will be handled by the changing of the selectedUser
        this.collection.sync = _.bind(this.sync, this);
    },

    bindDataChange: function() {
        // these are handlers that we only want to run when the parent module is forecasts
        if (!_.isUndefined(this.context.parent) && !_.isUndefined(this.context.parent.get('model'))) {
            if (this.context.parent.get('model').module == 'Forecasts') {
                this.before('render', function() {
                    // if manager is not set or manager == false
                    var ret = true;
                    if(_.isUndefined(this.selectedUser.isManager) || this.selectedUser.isManager == false) {
                        console.log('manager before render return false');
                        ret = false;
                    }

                    // only render if this.selectedUser.showOpps == false which means
                    // we want to display the manager worksheet view
                    if(ret) {
                        ret = !(this.selectedUser.showOpps);
                    }

                    if(ret === false && !this.layout.$el.hasClass('hide')) {
                        // hide the layout
                        console.log('manager beforeRender hide');
                        this.layout.hide();
                    }

                    console.log('manager showOpps before render return: ', ret);
                    return ret;
                }, true);
                this.on('render', function() {
                    var user = this.context.parent.get('selectedUser') || app.user.toJSON();
                    console.log('Manager Render:', user.isManager, user.showOpps);
                    if (user.isManager && user.showOpps == false) {
                        if(this.layout.$el.hasClass('hide')) {
                            console.log('Manager Show', user);
                            this.layout.show();
                        }

                        // insert the footer
                        if(!_.isEmpty(this.totals)) {
                            console.log('insert manager footer');
                            var tpl = app.template.getView('recordlist.totals', this.module);
                            this.$el.find('tbody').after(tpl(this));
                        }
                    } else {
                        if(!this.layout.$el.hasClass('hide')) {
                            console.log('Manager Hide', user);
                            this.layout.hide();
                        }
                    }
                }, this);

                this.context.parent.on('change:selectedUser', function(model, changed) {
                    // selected user changed
                    var doFetch = false;
                    if(this.selectedUser.id != changed.id) {
                        doFetch = true;
                    }
                    if(!doFetch && this.selectedUser.isManager != changed.isManager) {
                        doFetch = true;
                    }
                    if(!doFetch && this.selectedUser.showOpps != changed.showOpps) {
                        doFetch = !(changed.showOpps);
                    }
                    this.selectedUser = changed;

                    if(doFetch) {
                        this.collection.fetch();
                    } else {
                        if(this.selectedUser.isManager && this.selectedUser.showOpps == true && !this.layout.$el.hasClass('hide')) {
                            // viewing managers opp worksheet so hide the manager worksheet
                            this.layout.hide();
                        }
                    }
                }, this);
            }
        }

        this.collection.on('reset change', function() {
            this.calculateTotals();
        }, this);


        app.view.views.RecordlistView.prototype.bindDataChange.call(this);
    },

    sync: function(method, model, options) {

        if (!_.isUndefined(this.context.parent) && !_.isUndefined(this.context.parent.get('selectedUser'))) {
            var sl = this.context.parent.get('selectedUser');

            if(sl.isManager == false) {
                // they are not a manager, we should always hide this if it's not already hidden
                if(!this.layout.$el.hasClass('hide')) {
                    this.layout.hide();
                }
                return;
            }
        }

        var callbacks,
            url;

        options = options || {};

        options.params = options.params || {};

        if(!_.isUndefined(this.selectedUser.id)) {
            options.params.user_id = this.selectedUser.id;
        }

        options.limit = 1000;
        options = app.data.parseOptionsForSync(method, model, options);

        // custom success handler
        options.success = _.bind(function(model, data, options) {
            this.collectionSuccess(data);
        }, this);

        callbacks = app.data.getSyncCallbacks(method, model, options);
        this.trigger("data:sync:start", method, model, options);

        url = app.api.buildURL("ForecastManagerWorksheets", null, null, options.params);
        app.api.call("read", url, null, callbacks);
    },

    /**
     * Method to handle the success of a collection call to make sure that all reportee's show up in the table
     * even if they don't have data for the user that is asking for it.
     * @param data
     */
    collectionSuccess: function(data) {
        var records = [],
            users = $.map(this.selectedUser.reportees, function(obj) {
                return $.extend(true, {}, obj);
            });

        // put the selected user on top
        users.unshift({id: this.selectedUser.id, name: this.selectedUser.full_name});

        // get the base currency
        var currency_id = app.currency.getBaseCurrencyId();
        var currency_base_rate = app.metadata.getCurrency(app.currency.getBaseCurrencyId()).conversion_rate;

        _.each(users, function(user) {
            var row = _.find(data, function(rec) {
                return (rec.user_id == this.id)
            }, user);
            if(!_.isUndefined(row)) {
                // update the name on the row as this will have the correct formatting for the locale
                row.name = user.name;
            } else {
                row = _.clone(this.defaultValues);
                row.currency_id = currency_id;
                row.base_rate = currency_base_rate;
                row.user_id = user.id;
                row.assigned_user_id = this.selectedUser.id;
                row.draft = (this.selectedUser.id == app.user.id) ? 1 : 0;
                row.name = user.name;
            }
            records.push(row);
        }, this);

        this.collection.reset(records);
    },

    calculateTotals: function() {
        // add up all the currency fields
        if(this.collection.length == 0) {
            // no items, just bail
            return;
        }
        var fields = _.filter(this._fields.visible, function(field) {
                return field.type === 'currency';
            }),
            fieldNames = [];

        _.each(fields, function(field) {
            fieldNames.push(field.name);
            this.totals[field.name] = 0;
        }, this);

        if(!_.isUndefined(this.totals.likely_case)) {
            this.totals.likely_case_display = true
        }
        if(!_.isUndefined(this.totals.best_case)) {
            this.totals.best_case_display = true
        }
        if(!_.isUndefined(this.totals.worst_case)) {
            this.totals.worst_case_display = true
        }

        this.collection.each(function(model) {
            _.each(fieldNames, function(field) {
                // convert the value to base
                var val = model.get(field);
                if(_.isUndefined(val) || _.isNaN(val)) {
                    return;
                }
                val = app.currency.convertWithRate(val, model.get('base_rate'));
                this.totals[field] = app.math.add(this.totals[field], val);
            }, this)
        }, this);
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
