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
 * Editable List Button modifications that are specific to UserSignatures
 * When a row is updated to be the default, no other rows should be shown as
 * the default.
 *
 * @class View.Fields.Base.UserSignatures.EditablelistbuttonField
 * @alias SUGAR.App.view.fields.BaseUserSignaturesEditablelistbuttonField
 * @extends View.Fields.Base.EditablelistbuttonField
 */
({
    /**
     * Called when the model is successfully saved
     *
     * Need to refresh the list after save because we make changes to the user
     * preferences which causes a metadata refresh. If we don't refresh and a
     * user makes another inline edit save it will kick that back and cause
     * problems.
     *
     * @param {Data.Bean} model The updated model
     * @private
     */
    _onSaveSuccess: function(model) {
        this._super('_onSaveSuccess', [model]);
        this.closestComponent('filterpanel').trigger('filter:apply');
    }
})
