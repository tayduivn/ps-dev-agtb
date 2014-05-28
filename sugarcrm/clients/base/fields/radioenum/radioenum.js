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
 * @class View.Fields.Base.RadioenumField
 * @alias SUGAR.App.view.fields.BaseRadioenumField
 * @extends View.Fields.Base.EnumField
 */
({
    // On list-edit template,
    // we want the radio buttons to be replaced by a select so each method must call the EnumField method instead.
    extendsFrom: 'EnumField',
    plugins: ["ListEditable"],
    fieldTag: "input",

    _render: function(){
        this.loadEnumOptions(false, function() {
                if (!this.disposed) {
                    this.render();
                }
            }
        );
        app.view.Field.prototype._render.call(this);
        if(this.tplName === 'list-edit') {
            this._super("_render");
        }
    },
    bindDomChange: function() {
        if (this.tplName === 'list-edit') {
            this._super("bindDomChange");
        } else {
            if (!(this.model instanceof Backbone.Model)) return;
            var self = this;
            var el = this.$el.find(this.fieldTag);
            el.on("change", function() {
                self.model.set(self.name, self.unformat(self.$(self.fieldTag+":radio:checked").val()));
            });
        }
    },
    format: function(value) {
        if (this.tplName === 'list-edit') {
            return this._super("format", [value]);
        } else {
            return app.view.Field.prototype.format.call(this, value);
        }
    },
    unformat: function(value) {
        if (this.tplName === 'list-edit') {
            return this._super("unformat", [value]);
        } else {
            return app.view.Field.prototype.unformat.call(this, value);
        }
    },
    decorateError: function(errors) {
        if (this.tplName === 'list-edit') {
            return this._super("decorateError", [errors]);
        } else {

            var errorMessages = [],
                $tooltip;

            // Add error styling
            this.$el.closest('.record-cell').addClass('error');
            this.$el.addClass('error');
            // For each error add to error help block
            _.each(errors, function(errorContext, errorName) {
                errorMessages.push(app.error.getErrorString(errorName, errorContext));
            });
            this.$(this.fieldTag).last().closest('p').append(this.exclamationMarkTemplate(errorMessages));
            $tooltip = this.$('.error-tooltip');
            if (_.isFunction($tooltip.tooltip)) {
                var tooltipOpts = { container: 'body', placement: 'top', trigger: 'click' };
                $tooltip.tooltip(tooltipOpts);
            }
        }
    },
    clearErrorDecoration: function() {
        if (this.tplName === 'list-edit') {
            return this._super("clearErrorDecoration");
        } else {
            var ftag = this.fieldTag || '';
            // Remove previous exclamation then add back.
            this.$('.add-on').remove();
            this.$el.removeClass(ftag);
            this.$el.removeClass("error");
            this.$el.closest('.record-cell').removeClass("error");
        }
    }
})
