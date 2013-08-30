/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    extendsFrom: 'FieldsetField',

    /**
     * Mapping name field name to format initial
     *
     * @property {Object}
     */
    formatMap: {
        'first_name': 'f',
        'last_name': 'l',
        'salutation': 's'
    },

    /**
     * {@inheritDoc}
     * Sort the dependant fields by the user locale format order.
     */
    initialize: function(options) {
        var formatPlaceholder = app.user.getPreference('default_locale_name_format') || '';
        options.def.fields = _.sortBy(options.def.fields, function(field) {
            return formatPlaceholder.indexOf(this.formatMap[field.name]);
        }, this);
        app.view.invokeParent(this, {
            type: 'field',
            name: 'fieldset',
            method: 'initialize',
            args: [options]
        });
    },

    _loadTemplate: function() {
        app.view.invokeParent(this, {
            type: 'field',
            name: 'fieldset',
            method: '_loadTemplate'
        });
        //Bug: SP-1273 - Fixes Contacts subpanel record links to home page
        //(where expectation was to go to the corresponding Contact record)
        if (this.def.link) {
            var action = this.def.route && this.def.route.action ? this.def.route.action : '';
            //If `this.template` resolves to `base/list.hbs`, that template expects an
            //initialized `this.href`. That's normally handled by the `base.js` controller,
            //but, in this case, since `fullname.js` is controller, we must handle here.
            this.href = '#' + app.router.buildRoute(this.module||this.context.get('module'), this.model.id, action, this.def.bwcLink);
        }
        this.template = app.template.getField(this.type, this.view.name + '-' + this.tplName, this.model.module) ||
                        this.template;
    },

    /**
     * {@inheritDoc}
     * Returns a single placeholder instead of fieldset placeholder
     * since fullname field generates children placeholder on render.
     */
    getPlaceholder: function() {
        return app.view.Field.prototype.getPlaceholder.call(this);
    },

    /**
     * {@inheritDoc}
     * Since fullname field generates children field components
     * each rendering time, it should dispose the previous generated items
     * before it renders children placeholders.
     */
    _render: function() {
        _.each(this.fields, function(field) {
            field.dispose();
            delete this.view.fields[field.sfId];
        }, this);
        this.fields = [];
        var fieldSize = _.size(this.view.fields);
        app.view.Field.prototype._render.call(this);
        //skip bind the children placeholder unless the template contains child fields
        if (_.size(this.view.fields) === fieldSize) {
            return this;
        }
        _.each(this.def.fields, function(field) {
            var field = this.view.getField(field.name);
            if (_.isEmpty(field)) {
                return;
            }
            this.fields.push(field);
            field.setElement(this.$("span[sfuuid='" + field.sfId + "']"));
            field.render();
        }, this);
        return this;
    },

    /**
     * {@inheritDoc}
     * Format name parts to current user locale.
     */
    format: function(name) {
        return app.utils.formatNameLocale({
            first_name: this.model.get('first_name'),
            last_name: this.model.get('last_name'),
            salutation: this.model.get('salutation')
        });
    },

    /**
     * @override
     * Note that the parent bindDataChange (from FieldsetField) is an empty function
     */
    bindDataChange: function() {
        if (this.model) {
            // As detail templates don't contain Sidecar Fields,
            // we need to rerender this field in order to visualize the changes
            this.model.on("change:" + this.name, function() {
                if (this.fields.length === 0) {
                    this.render();
                }
            }, this);
            // When a child field changes, we need to update the full_name value
            _.each(this.def.fields, function(field) {
                this.model.on("change:" + field.name, this.updateValue, this);
            }, this);
        }
    },

    /**
     * Update the value of this parent field when a child changes
     */
    updateValue: function() {
        this.model.set(this.name, this.format());
    }
})
