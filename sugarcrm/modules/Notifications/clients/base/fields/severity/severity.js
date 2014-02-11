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
({
    /**
     * Severity Widget.
     *
     * Extends from EnumField widget adding style property according to specific
     * severity.
     */
    extendsFrom: 'EnumField',

    /**
     * An object where its keys map to specific severity and values to matching
     * CSS classes.
     *
     * @property {Object}
     * @protected
     */
    _styleMapping: {
        'default': '',
        alert: 'label-important',
        information: 'label-info',
        other: 'label-inverse',
        success: 'label-success',
        warning: 'label-warning'
    },

    /**
     * {@inheritDoc}
     *
     * Listen to changes on `is_read` field only if view name matches
     * notifications.
     */
    bindDataChange: function() {
        this._super('bindDataChange');

        if (this.model && this.view.name === 'notifications') {
            this.model.on('change:is_read', this.render, this);
        }
    },

    /**
     * {@inheritDoc}
     *
     * Inject additional logic to load templates based on different view names
     * according to the following:
     *
     * - `fields/severity/<view-name>-<tpl-name>.hbs`
     * - `fields/severity/<view-template-name>-<tpl-name>.hbs`
     */
    _loadTemplate: function() {
        this._super('_loadTemplate');

        var template = app.template.getField(
            this.type,
            this.view.name + '-' + this.tplName,
            this.model.module
        );

        if (!template && this.view.meta && this.view.meta.template) {
            template = app.template.getField(
                this.type,
                this.view.meta.template + '-' + this.tplName,
                this.model.module
            );
        }

        this.template = template || this.template;
    },

    /**
     * {@inheritDoc}
     *
     * Defines style property based on field value.
     */
    _render: function () {
        var severity = this.model.get(this.name);
        this.severityCss = this._styleMapping[severity] || this._styleMapping.default;
        this._super('_render');
    }
})
