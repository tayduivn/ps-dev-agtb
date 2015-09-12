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
 * Currencies Record List.
 *
 * @class View.Views.Base.Currencies.RecordlistView
 * @alias SUGAR.App.view.views.BaseCurrenciesRecordlistView
 * @extends View.Views.Base.RecordlistView
 */
({
    extendsFrom: 'RecordlistView',

    /**
     * @inheritdoc
     **/
    bindDataChange: function() {
        this.collection.on('data:sync:complete', function() {
            this.collection.each(function(model) {
                if (model.get('id') == '-99') {
                    model.isDefault = true;
                    var defaultLang = app.lang.get("LBL_DEFAULT", "Currencies");
                    if(model.get('name').indexOf(defaultLang) === -1) {
                        // todo: Fix this because this will not be RTL-friendly
                        model.set('name', model.get('name') + ' (' + defaultLang + ')');
                    }
                }
            }, this);

            this.render();
        }, this);

        // call the parent
        this._super("bindDataChange");
    },

    /**
     * @inheritdoc
     **/
    _render: function() {
        this._super("_render");

        // row checkbox
        var $cb = this.$('tr[name="Currencies_-99"] input[name="check"]');
        // row action dropdown
        var $actions = this.$('tr[name="Currencies_-99"] a.dropdown-toggle');

        // disable the checkbox
        if ($cb.length) {
            $cb.prop('disabled', true);
        }

        // remove actions
        if ($actions.length) {
            $actions.remove();
        }
    },

})
