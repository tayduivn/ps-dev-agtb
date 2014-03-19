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
            this.parent_link = (fieldTypeReq === 'all' ? 'docs/index-forms' : 'field/all');
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

        this._super('_render');

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
                        if (!fieldObject.plugins || !_.contains(fieldObject.plugins, "ListEditable") || fieldLayout !== 'list') {
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
