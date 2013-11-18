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
    className: 'container-fluid',
    section: {},
    useTable: true,
    parent_link: '',
    tempfields: [],

    _render: function() {
        var self = this,
            fieldTypeReq = this.context.get('field_type'),
            fieldTypes = (fieldTypeReq === 'all' ? ['text','bool','date','datetimecombo','currency','email'] : [fieldTypeReq]),
                //'textarea','url','phone','password','full_name'
            fieldStates = ['detail','edit','error','disabled'],
            fieldLayouts = ['base','record','list'],
            fieldMeta = {};

            this.section.title = 'Form Elements';
            this.section.description = 'Basic fields that support detail, record, and edit modes with error addons.';
            this.useTable = (fieldTypeReq === 'all' ? true : false);
            this.parent_link = (fieldTypeReq === 'all' ? 'docs/index.forms' : 'field/all');
            this.tempfields = [];

        _.each(fieldTypes, function(fieldType){

            //build meta data for field examples from model fields
            fieldMeta = _.find(self.model.fields, function(field) {
                if (field.type === 'datetime' && fieldType.indexOf('date') === 0) {
                    field.type = fieldType;
                }
                return field.type === fieldType;
            }, self);

            //insert metadata into array for hbs template
            if (fieldMeta) {
                var metaData = self.meta['template_values'][fieldType];

                if (_.isObject(metaData) && !_.isArray(metaData)) {
                    _.each(metaData, function(value, name) {
                        self.model.set(name, value);
                    }, self);
                } else {
                    self.model.set(fieldMeta.name, metaData);
                }

                self.tempfields.push(fieldMeta);
            }
        });

        app.view.View.prototype._render.call(this);

        //render example fields into accordion grids
        //e.g., ['text','bool','date','datetimecombo','currency','email']
        _.each(fieldTypes, function(fieldType){

            var fieldMeta = _.find(self.tempfields, function(field) {
                    return field.type === fieldType;
                }, self);

            //e.g., ['detail','edit','error','disabled']
            _.each(fieldStates, function(fieldState) {

                //e.g., ['base','record','list']
                _.each(fieldLayouts, function(fieldLayout) {
                    var fieldTemplate = fieldState;

                    //set field view template name
                    if (fieldLayout === 'list') {
                        if (fieldState === 'edit') {
                            fieldTemplate = 'list-edit';
                        } else {
                            fieldTemplate = 'list';
                        }
                    } else if (fieldState === 'error') {
                        fieldTemplate = 'edit';
                    }

                    var fieldSettings = {
                            view: self,
                            def: {
                                name: fieldMeta.name,
                                type: fieldType,
                                view: fieldTemplate,
                                default: true,
                                enabled: fieldState === 'disabled' ? false : true
                            },
                            viewName: fieldTemplate,
                            context: self.context,
                            module: self.module,
                            model: self.model,
                            meta: fieldMeta
                        };

                    var fieldObject = app.view.createField(fieldSettings),
                        fieldDivId = '#' + fieldType + '_' + fieldState + '_' + fieldLayout;

                    //pre render field setup
                    if (fieldState !== 'detail') {
                        if (fieldObject.extendsFrom !== 'ListeditableField' || fieldLayout !== 'list') {
                            fieldObject.setMode('edit');
                        } else {
                            fieldObject.setMode('list-edit');
                        }
                    }
                    if (fieldState === 'disabled') {
                        fieldObject.setDisabled(true);
                    }

                    //render field
                    self.$(fieldDivId).append(fieldObject.el);
                    fieldObject.render();

                    //post render field setup
                    if (fieldType === 'currency' && fieldState === 'edit') {
                        fieldObject.setMode('edit');
                    }
                    if (fieldState === 'error') {
                        if (fieldType === 'email') {
                            var errors = {email: ['primary@example.info']};
                            fieldObject.decorateError(errors);
                        } else {
                            fieldObject.setMode('edit');
                            fieldObject.decorateError('You did a bad, bad thing.');
                        }
                    }
                });

            });

            if (fieldTypeReq !== 'all') {
                self.title = fieldTypeReq + ' field';
                var descTpl = app.template.getView('styleguide.' + fieldTypeReq, self.module);
                if (descTpl) {
                    self.description = descTpl();
                }
            }
        });
    }
})
