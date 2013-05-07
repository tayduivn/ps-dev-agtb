//FILE SUGARCRM flav=pro ONLY
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
    extendsFrom : 'RecordlistView',

    /**
     * We have to overwrite this method completely, since there is currently no way to completely disable
     * a field from being displayed
     *
     * @returns {{default: Array, available: Array, visible: Array, options: Array}}
     */
    parseFields : function() {
        var catalog = {
            'default': [], //Fields visible by default
            'available': [], //Fields hidden by default
            'visible': [], //Fields user wants to see,
            'options': []
        };

        // TODO: load field prefs and store names in this._fields.available.visible
        // no prefs so use viewMeta as default and assign hidden fields
        _.each(this.meta.panels, function (panel) {
            _.each(panel.fields, function (fieldMeta, i) {
                var addField = true;
                if(addField) {
                    if(app.metadata.getModule("Forecasts", "config").is_setup) {
                        if(fieldMeta.name.indexOf('_case') != -1) {
                            var field = 'show_worksheet_' + fieldMeta.name.replace('_case', '');
                            addField = (app.metadata.getModule("Forecasts", "config")[field] == 1);
                        }
                    } else {
                        // forecast is not setup,
                        addField = !(fieldMeta.name == "commit_stage");
                    }
                    if (addField) {
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
                }
            }, this);
        }, this);
        return catalog;
    }
})

