/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @inheritDoc
 *
 * @class View.Views.PortalModuleListLayout
 * @alias SUGAR.App.view.layouts.PortalModuleListLayout
 */
({
    extendsFrom: 'moduleListLayout',

    /**
     * @inheritDoc
     *
     * Overloading this because for portal we dont need to add or remove
     * unmapped modules.
     * See the second `return this;` statement.
     */
    _setActiveModule: function(module) {

        if (_.isEmpty(this._components)) {
            // wait until we have the mega menu in place
            return this;
        }

        var mappedModule = app.metadata.getTabMappedModule(module);

        this.$('[data-container=module-list]').children('.active').removeClass('active');
        // for portal don't add unmapped modules
        if (!this._catalog[mappedModule]) {
            return;
        }

        this._catalog[mappedModule].long.addClass('active');
        this.toggleModule(mappedModule, true);

        return this;
    }

})
