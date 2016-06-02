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
 * The AddresseesSelectionListView provides an easy way to select a record from a list.
 * It's designed to be used in a drawer. The model attributes of the selected
 * record will be passed to the drawer callback.
 *
 * AddresseesSelectionListView doesn't check ACL in _getModelAttributes
 *
 * @class View.Views.Base.Addressees.SelectionListView
 * @alias SUGAR.App.view.views.BaseAddresseesSelectionListView
 * @extends View.Views.Base.SelectionListView
 */
({
    extendsFrom: 'SelectionListView',

    /**
     * @inheritdoc
     * @override ACL check for fields disabled.
     */
    _getModelAttributes: function(model) {
        var attributes = {
            id: model.id,
            value: model.get('name')
        };

        _.each(model.attributes, function(value, field) {
            attributes[field] = attributes[field] || model.get(field);
        }, this);
        return attributes;
    }
})
