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
    events: {
        'click .btn.layout' : 'layoutClicked'
    },
    plugins: ['Tooltip'],
    extendsFrom: 'ButtonField',
    getFieldElement: function() {
        return this.$el;
    },
    _render: function() {
        var buttonField = app.view._getController({type: 'field', name: 'button', platform: app.config.platform});
        buttonField.prototype._render.call(this);
    },
    _loadTemplate: function() {
        app.view.Field.prototype._loadTemplate.call(this);
        if(this.action !== 'edit' || (this.model.maxColumns <= 1)) {
            this.template = app.template.empty;
        }
    },
    format: function(value) {
        var metadata = this.model.get("metadata");
        if(metadata) {
            return (metadata.components) ? metadata.components.length : 1;
        }
        return value;
    },
    layoutClicked: function(evt) {
        var value = $(evt.currentTarget).data('value');
        this.setLayout(value);
    },
    setLayout: function(value) {
        var span = 12 / value;
        if(this.value) {

            if (value === this.value) {
                return;
            }
            var setComponent = function() {
                var metadata = this.model.get("metadata");

                _.each(metadata.components, function(component){
                    component.width = span;
                }, this);

                if(metadata.components.length > value) {
                    _.times(metadata.components.length - value, function(index){
                        metadata.components[value - 1].rows = metadata.components[value - 1].rows.concat(metadata.components[value + index].rows);
                    },this);
                    metadata.components.splice(value);
                } else {
                    _.times(value - metadata.components.length, function(index) {
                        metadata.components.push({
                            rows: [],
                            width: span
                        });
                    }, this);
                }
                this.model.set("metadata", app.utils.deepCopy(metadata), {silent: true});
                this.model.trigger("change:metadata");
            };
            if(value !== this.value) {
                app.alert.show('resize_confirmation', {
                    level: 'confirmation',
                    messages: app.lang.get('LBL_DASHBOARD_LAYOUT_CONFIRM', this.module),
                    onConfirm: _.bind(setComponent, this),
                    onCancel: _.bind(this.render,this) // reverse the toggle done
                });
            } else {
                setComponent.call(this);
            }
        } else {
            //new data
            var metadata = {
                components: []
            };
            _.times(value, function(index) {
                metadata.components.push({
                    rows: [],
                    width: span
                });
            }, this);

            this.model.set("metadata", app.utils.deepCopy(metadata), {silent: true});
            this.model.trigger("change:metadata");
        }
    },
    bindDomChange: function() {

    },
    bindDataChange: function() {
        if (this.model) {
            this.model.on("change:metadata", this.render, this);
            if(this.model.isNew()) {
                //Assign default layout set
                this.setLayout(1);
                //clean out model changed attributes not to warn unsaved changes
                this.model.changed = {};
            }
        }
    }
})
