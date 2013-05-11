/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    // On list-edit template,
    // we want the radio buttons to be replaced by a select so each method must call the EnumField method instead.
    extendsFrom: 'ListeditableField',
    _render: function(){
        // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
        var options = app.view.invokeParent(this, {type: 'field', name: 'enum', method: 'loadEnumOptions', args: [false,
            function() {
                if(!this.disposed){
                    this.render();
                }
            }]
        });
        app.view.Field.prototype._render.call(this);
        if(this.tplName === 'list-edit') {
            var optionsKeys = _.isObject(options) ? _.keys(options) : [];
            // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
            var select2Options = app.view.invokeParent(this, {type: 'field', name: 'enum', method: 'getSelect2Options', args: [optionsKeys]});
            this.$(this.fieldTag).select2(select2Options);
            this.$(".select2-container").addClass("tleft");
        }
    },
    bindDomChange: function() {
        if (this.tplName === 'list-edit') {
            // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
            app.view.invokeParent(this, {type: 'field', name: 'enum', method: 'bindDomChange'});
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
            // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
            return app.view.invokeParent(this, {type: 'field', name: 'enum', method: 'format', args: [value]});
        } else {
            return app.view.Field.prototype.format.call(this, value);
        }
    },
    unformat: function(value) {
        if (this.tplName === 'list-edit') {
            // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
            return app.view.invokeParent(this, {type: 'field', name: 'enum', method: 'unformat', args: [value]});
        } else {
            return app.view.Field.prototype.unformat.call(this, value);
        }
    },
    _loadTemplate: function() {
        app.view.invokeParent(this, {type: 'field', name: 'listeditable', method: '_loadTemplate'});

        //Important to change the fieldTag to bind the dom "change" event
        if(this.tplName === 'list-edit') {
            this.fieldTag = 'select';
        } else {
            this.fieldTag = 'input';
        }
    },
    decorateError: function(errors) {
        if (this.tplName === 'list-edit') {
            return app.view.invokeParent(this, {type: 'field', name: 'enum', method: 'decorateError', args: [errors]});
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
            // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
            return app.view.invokeParent(this, {type: 'field', name: 'enum', method: 'clearErrorDecoration'});
        } else {
            var ftag = this.fieldTag || '';
            // Remove previous exclamation then add back.
            this.$('.add-on').remove();
            this.$el.removeClass(ftag);
            this.$el.removeClass("error");
            this.$el.closest('.record-cell').removeClass("error");
        }
    },
    unbindDom: function() {
        this.$(this.fieldTag).select2('destroy');
        app.view.Field.prototype.unbindDom.call(this);
    }
})