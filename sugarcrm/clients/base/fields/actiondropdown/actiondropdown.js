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
    extendsFrom: 'FieldsetField',
    fields: null,
    getPlaceholder: function() {
        var placeholder = app.view.Field.prototype.getPlaceholder.call(this);
        var $container = $(placeholder.toString()),
            $caret = $('<a class="btn dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);"><span class="icon-caret-down"></span></a>'),
            $dropdown = $('<ul class="dropdown-menu"><span class="ext"></span></ul>');

        if(this.def.primary) {
            $caret.addClass('btn-primary');
        }

        _.each(this.def.buttons, function(fieldDef, index) {
            var field = app.view.createField({
                def: fieldDef,
                view: this.view,
                viewName: this.options.viewName,
                model: this.model
            });
            this.fields.push(field);
            field.on('show hide', this.setPlaceholder, this);
            field.parent = this;
            if(index == 0) {
                $container.append(field.getPlaceholder().toString());
            } else {
                if(index == 1) {
                    $container.addClass('actions btn-group')
                        .append($caret)
                        .append($dropdown);
                }
                $dropdown.append('<li>' + field.getPlaceholder().toString() + '</li>');
            }

        }, this);
        return new Handlebars.SafeString($container.get(0).outerHTML);
    },
    _render: function() {
        app.view.fields.FieldsetField.prototype._render.call(this);
        this.setPlaceholder();
    },
    setPlaceholder: function() {
        var index = 0;
        _.each(this.fields, function(field){
            var fieldPlaceholder = this.$("span[sfuuid='" + field.sfId + "']");
            if(field.isHidden) {
                fieldPlaceholder.toggleClass('hide', true);
                this.$el.append(fieldPlaceholder);
            } else {
                fieldPlaceholder.toggleClass('hide', false);
                if(index == 0) {
                    field.getFieldElement().addClass("btn");
                    if(this.def.primary) {
                        field.getFieldElement().addClass("btn-primary");
                    }
                    this.$el.prepend(fieldPlaceholder);
                } else {
                    field.getFieldElement().removeClass("btn btn-primary");
                    this.$(".dropdown-menu").prepend($('<li>').append(fieldPlaceholder));
                }
                index++;
            }
        }, this);

        if(index <= 1) {
            this.$(".dropdown-toggle").hide();
            this.$el.removeClass('btn-group');
        } else {
            this.$(".dropdown-toggle").show();
            this.$el.addClass('btn-group');
        }
        this.$(".dropdown-menu").children("li").each(function(index, el){
            if($(el).html() === '') {
                $(el).remove();
            }
        });
    },
    setDisabled: function(disable) {
        app.view.fields.FieldsetField.prototype.setDisabled.call(this, disable);
        disable = _.isUndefined(disable) ? true : disable;
        if (disable) {
            this.$('.dropdown-toggle').addClass('disabled');
        } else {
            this.$('.dropdown-toggle').removeClass('disabled');
        }
    }
})
