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
    parent_link: '',
    useTable: true,
    tempfields: [],
    errorfields: [],

    _render: function() {
        this.section.title = 'Form Elements';
        this.section.description = 'Basic fields that support detail, record, and edit modes with error addons.';

        var self = this,
            fieldTypeReq = this.context.get('field_type'),
            fieldTypes = (fieldTypeReq === 'all' ? ['text','bool','email','date','currency'] : [fieldTypeReq]),
            errors = {required:true, 'This is an error message.':{}},
            errorMeta = {},
            fieldMeta = {};

            this.useTable = (fieldTypeReq === 'all' ? true : false);
            this.parent_link = (fieldTypeReq === 'all' ? 'docs/index.forms' : 'field/all');
            this.tempfields = [];
            this.errorfields = [];

        _.each(fieldTypes, function(fieldType){

            fieldMeta = _.find(self.model.fields, function(field) {
                if(field.type === 'datetime' && fieldType.indexOf('date') === 0 ) {
                    field.type = fieldType;
                }
                return field.type == fieldType;
            }, self);

            if (fieldMeta) {

                if(_.isObject(self.meta['template_values'][fieldType]) && !_.isArray(self.meta['template_values'][fieldType])) {
                    _.each(self.meta['template_values'][fieldType], function(value, name) {
                        self.model.set(name, value);
                    }, self);
                } else {
                    self.model.set(fieldMeta.name, self.meta['template_values'][fieldType]);
                }

                //self.model.trigger('error:validation:' + fieldMeta.name + '_ERROR', errors);

                errorMeta = app.utils.deepCopy(fieldMeta);
                errorMeta.name = fieldMeta.name + '_ERROR';

                fieldMeta.errorMeta = [];
                fieldMeta.errorMeta.push(errorMeta);

                self.tempfields.push(fieldMeta);
            }

        });

        if (fieldTypeReq !== 'all') {
            this.title = fieldTypeReq + ' field';
            var descTpl = app.template.getView('styleguide.' + fieldTypeReq, 'Styleguide');
            if (descTpl) {
                this.description = descTpl();
            }
        }

        app.view.View.prototype._render.call(this);

        _.each(this.fields, function(field){
            if(field.tplName === 'edit') {
                field.setMode('edit');
            }
            if(field.tplName === 'disabled') {
                field.setDisabled(true);
                field.setMode('edit');
            }
            if(field.tplName === 'error') {
                field.setMode('edit');
                self.model.trigger('error:validation:' + field.name, errors);
            }
        }, this);

    }
})
