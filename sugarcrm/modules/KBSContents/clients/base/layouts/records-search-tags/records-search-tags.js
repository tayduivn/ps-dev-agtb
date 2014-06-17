/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
({
    initialize: function(options) {
        this._super('initialize', [options]);
        this._initializeCollectionFilterDef(options);
    },

    /**
     * Initialize collection in the sub-sub-component recordlist
     * with specific filterDef using tags for build recordlist
     * filtered by tags.
     *
     * @param {Object} options
     * @private
     */
    _initializeCollectionFilterDef: function(options) {
        if (_.isUndefined(options.def.context.tag)) {
            return;
        }

        var filterDef = {
            filter: [{
                tags: {
                    $in: [{
                        id: false,
                        name: options.def.context.tag
                    }]
                },
                active_rev: {
                    $equals: 1
                }
            }]
        };

        var chain = ['sidebar', 'main-pane', 'list', 'recordlist'];
        var recordList = _.reduce(chain, function(component, name) {
            if (!_.isUndefined(component)) {
                return component.getComponent(name);
            }
        }, this);

        if (!_.isUndefined(recordList)) {
            recordList.collection.filterDef = filterDef;
        }
    }
})
