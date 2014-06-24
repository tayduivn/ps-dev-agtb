/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
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
