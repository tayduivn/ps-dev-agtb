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
 * @class View.Fields.Base.EditrecurrencesbuttonField
 * @alias SUGAR.App.view.fields.BaseEditrecurrencesbuttonField
 * @extends View.Fields.Base.Rowaction
 */
({
    extendsFrom: 'RowactionField',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.type = 'rowaction';
    },

    /**
     * Checks whether the user has both the access to the model and the record is a recurring type
     * and record is the parent record in the recurring series //todo: remove with MAR-2268
     * @inheritdoc
     */
    hasAccess: function() {
        var acl = this._super('hasAccess');
        return (
            acl &&
            _.isEmpty(this.model.get('repeat_parent_id')) && //todo: remove with MAR-2268
            !_.isEmpty(this.model.get('repeat_type'))
        );
    },

    /**
     * Re-render the field when the status on the record changes.
     */
    bindDataChange: function() {
        if (this.model) {
            this.model.on('change:repeat_type', this.render, this);
        }
    },

    /**
     * Event handler for editing all recurring records of a series
     * @inheritdoc
     */
    rowActionSelect: function() {
        this.context.trigger('all_recurrences:edit');
    }
})
