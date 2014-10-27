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
    extendsFrom: 'EnumField',

    /**
     * @inheritDoc
     *
     * @private
     */
    _render: function() {
        this._ensureSelectedValueInItems();
        //use enum field templates
        this.type = 'enum';
        this._super('_render');
    },

    /**
     * Meeting type is a special case where we want to ensure the selected
     * value is an option in the list. This can happen when User A has
     * an external meeting integration set up (ie. WebEx) and sets WebEx as
     * the type. If User B does not have WebEx set up (only needed to create
     * WebEx meetings, not to join), User B should still see WebEx selected
     * on existing meetings, but not be able to create a meeting with WebEx.
     *
     * @private
     */
    _ensureSelectedValueInItems: function() {
        var value = this.model.get(this.name),
            meetingTypeLabels;

        //if we don't have items list yet or no value previously selected - no work to do
        if (!this.items || _.isEmpty(this.items) || _.isEmpty(value)) {
            return;
        }

        //if selected value is not in the list of items, but is in the list of meeting types...
        meetingTypeLabels = app.lang.getAppListStrings('eapm_list');
        if (_.isEmpty(this.items[value]) && !_.isEmpty(meetingTypeLabels[value])) {
            //...add it to the list
            this.items[value] = meetingTypeLabels[value];
        }
    }
})
