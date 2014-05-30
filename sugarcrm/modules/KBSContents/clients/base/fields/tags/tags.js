/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    extendsFrom: 'TagsField',

    events: {
        'click [data-action=search-by-tag]': 'searchByTag'
    },

    searchByTag: function(e) {
        var tag = $(e.currentTarget).data('tag');
        var module = this.context.get('module');
        var def = {
            layout: 'records-search-tags',
            context: {
                module: module,
                tag: tag,
                forceNew: true
            }
        };

        if (app.drawer.count() && app.drawer.isActive(this.$el)) {
            app.drawer.load(def);
        } else {
            app.drawer.open(def);
        }
    }
})
