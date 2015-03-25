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
        'draft': 'label-pending',
        'in-review': 'label-warning',
        'approved': 'label-info',
        'published-in': 'label-published',
        'published-ex': 'label-success',
        'expired': 'label'
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
            this.model.set(this.name, 'draft', {silent: true});
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

    /**
     * {@inheritDoc}
     */
    _checkAccessToAction: function(action) {
        var access = this._super('_checkAccessToAction');
        if (access) {
            access = app.acl.hasAccessToModel('edit', this.model, this.name);
        }
        return access;
    }
})
