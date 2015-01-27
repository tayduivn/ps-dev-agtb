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
    //className: 'accordion-heading convert-options',

    /**
     * @inheritdoc
     */
    _render: function() {
        var transferActivitiesAction = app.metadata.getConfig().leadConvActivityOpt;
        this.transferLabel = (transferActivitiesAction === 'move') ?
            'LBL_CONVERT_MOVE_RELATED_ACTIVITIES' :
            'LBL_CONVERT_COPY_RELATED_ACTIVITIES';

        if (transferActivitiesAction !== 'donothing') {
            this._super('_render');
        }
    }
})
