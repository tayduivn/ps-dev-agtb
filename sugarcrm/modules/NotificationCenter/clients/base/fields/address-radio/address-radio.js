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
 * @class View.Fields.Base.NotificationCenterAddressRadioField
 * @alias SUGAR.App.view.fields.BaseNotificationCenterAddressField
 * @extends View.Fields.Base.NotificationCenterAddressBaseField
 */
({
    extendsFrom: 'NotificationCenterAddressBaseField',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.fieldTag = 'input[type=radio][name=' + options.def.name + ']';
    },

    /**
     * @inheritdoc
     */
    bindDomChange: function() {
        if (!this.model) {
            return;
        }
        var self = this;
        var el = this.$el.find(this.fieldTag);
        el.on('change', function() {
            self.setSelectedAddresses(self.$el.find(self.fieldTag + ':checked').val());
        });
    }
});
