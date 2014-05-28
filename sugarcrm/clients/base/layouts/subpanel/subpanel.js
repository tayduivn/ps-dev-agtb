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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Layouts.Base.SubpanelLayout
 * @alias SUGAR.App.view.layouts.BaseSubpanelLayout
 * @extends View.Layouts.Base.PanelLayout
 */
({
    extendsFrom: 'PanelLayout',

    /**
     * @override
     */
    initialize: function(opts) {
        opts.type = 'panel';
        //Check for the override_subpanel_list_view from the parent layout metadata and replace the list view if found.
        if (opts.meta && opts.def && opts.def.override_subpanel_list_view) {
            _.each(opts.meta.components, function(def) {
                if (def.view && def.view == 'subpanel-list') {
                    def.view = opts.def.override_subpanel_list_view;
                }
            });
            // override last_state.id with "override_subpanel_list_view" for unique state name.
            if (opts.meta.last_state.id) {
                opts.meta.last_state.id = opts.def.override_subpanel_list_view;
            }
        }

        if (opts.meta && opts.def && opts.def.override_paneltop_view) {
            _.each(opts.meta.components, function(def) {
                if (def.view && def.view == 'panel-top') {
                    def.view = opts.def.override_paneltop_view;
                }
            });
        }

        this._super("initialize", [opts]);

        // binding so subpanels can trigger other subpanels to reload by link name
        // example: ctx.trigger('subpanel:reload', {links: ['opportunities','revenuelineitems']});
        if (this.context.parent) {
            this.context.parent.on('subpanel:reload', function(args) {
                if (!_.isUndefined(args) && _.isArray(args.links) && _.contains(args.links, this.context.get('link'))) {
                    this.context.reloadData({recursive: false});
                }
            }, this);
        }
    }
})
