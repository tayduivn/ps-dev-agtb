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
        alert: 'label-important ',
        information: 'label-info ',
        other: 'label-inverse ',
        success: 'label-success ',
        warning: 'label-warning '
    },

    /**
     * {@inheritDoc}
     *
     * Defines style property based on field value.
     */
    _render: function () {
        var severity = this.model.get(this.name);
        this.severityCss = this._styleMapping[severity] || this._styleMapping.default;
        this.severityCss += 'label ellipsis_inline';
        this._super('_render');
    }
})
