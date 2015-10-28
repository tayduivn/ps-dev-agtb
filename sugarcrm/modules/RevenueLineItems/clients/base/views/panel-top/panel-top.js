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
({
    extendsFrom: 'PanelTopView',
    
    /**
     * @inheritdoc
     */
    initialize: function(options){
        this._super("initialize", [options]);
        if (this.parentModule == "Accounts") {
            this.meta.buttons = _.filter(this.meta.buttons, function(item){
                if (item.type != "actiondropdown") {
                    return true;
                }
                return false;
            });
        }
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');
        this.context.parent.on('subpanel:reload', function(args) {
            if (!_.isUndefined(args) && _.isArray(args.links) && _.contains(args.links, this.context.get('link'))) {
                // have to set skipFetch to false so panel.js will toggle this panel open
                this.context.set('skipFetch', false);
                this.context.reloadData({recursive: false});
            }
        }, this);
    },
    
    /**
     * @inheritdoc
     */
    createRelatedClicked: function(event) {
        // close RLI warning alert
        app.alert.dismiss('opp-rli-create');

        this._super("createRelatedClicked", [event]);
    }
})
