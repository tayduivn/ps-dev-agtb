/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * The view for Admin AWS Connect footer
 *
 * @class View.Views.Base.AdministrationAwsConnectFooterView
 * @alias SUGAR.App.view.views.BaseAdministrationAwsConnectFooterView
 * @extends View.View
 */
({
    /**
     * The name and help text labels
     */
    helpLabels: [
        {
            name: 'LBL_AWS_CONNECT_INST_NAME',
            text: 'LBL_AWS_CONNECT_INST_NAME_HELP_TEXT',
        },
        {
            name: 'LBL_AWS_CONNECT_REGION',
            text: 'LBL_AWS_CONNECT_REGION_HELP_TEXT',
        },
    ],

    /**
     * The help strings to be displayed in the help block
     */
    helpBlock: [],

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.helpBlock = this.generateHelpBlock();
    },

    /**
     * Creates and returns the strings for the help block
     *
     * @return array
     */
    generateHelpBlock() {
        var block = [];

        _.each(this.helpLabels, function(label) {
            block.push({
                name: app.lang.get(label.name, this.module) + ':',
                text: app.lang.get(label.text, this.module),
            });
        }, this);

        return block;
    },
})
