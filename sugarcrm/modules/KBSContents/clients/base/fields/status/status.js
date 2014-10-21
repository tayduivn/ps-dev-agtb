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
     * status Widget.
     *
     * Extends from EnumField widget adding style property according to specific
     * status.
     */
    extendsFrom: 'EnumField',

    /**
     * An object where its keys map to specific status and color to matching
     * CSS classes.
     *
     * @property {Object}
     * @protected
     */
    _statusClass: {
        'draft': 'st-draft',
        'in-review': 'st-review',
        'approved': 'st-approved',
        'published': 'st-pub',
        'published-in': 'st-pub-in',
        'published-ex': 'st-pub-ex',
        'expired': 'st-expired'
    },

    /**
     * {@inheritDoc}
     *
     * Defines `statusColor` property based on field value. If current status
     * does not match a known value its value is used as label and default
     * style is used as well.
     */
    _render: function() {
        if (!this.model.has(this.name)) {
            this.model.set(this.name, 'draft');
        }

        var status = this.model.get(this.name),
            options = app.lang.getAppListStrings(this.def.options);

        this.statusClass = this._statusClass[status];
        this.statusLabel = options[status] || status;

        this._super('_render');
    },

    /**
     * {@inheritDoc}
     */
    focus: function() {
        var self = this;
        if (this.action !== 'disabled' && !this.def.isMultiSelect) {
            _.defer(function() {
                self.$(self.fieldTag).select2('open');
            });
        }
    },

    _checkAccessToAction: function(action) {
        var access = this._super('_checkAccessToAction');
        if (access) {
            access = app.acl.hasAccessToModel('edit', this.model, this.name);
        }
        return access;
    }
})
