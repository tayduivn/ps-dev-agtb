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
    dropdownFields: null,
    events: {
        'click [data-toggle=dropdown]' : 'renderDropdown',
        'change [data-toggle=dropdownmenu]' : 'dropdownSelected',
        'touchstart [data-toggle=dropdownmenu]' : 'renderDropdown'
    },
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'field', name: 'fieldset', method: 'initialize', args:[options]});
        this.dropdownFields = [];

        //Throttle the setPlaceholder function per instance of this field.
        // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
        var actiondropdownField = app.view._getController({type:'field',name:'actiondropdown'});
        this.setPlaceholder = _.throttle(actiondropdownField.prototype.setPlaceholder, 100);
    },
    renderDropdown: function() {
        if(_.isEmpty(this.dropdownFields)) {
            return;
        }

        _.each(this.dropdownFields, function(field) {
            this.view.fields[field.sfId] = field;
            field.setElement(this.$("span[sfuuid='" + field.sfId + "']"));
            field.render();
        }, this);
        this.dropdownFields = null;
    },
    dropdownSelected: function(evt) {
        var $el = this.$(evt.currentTarget),
            selectedIndex = $el.val();
        if(!selectedIndex) {
            return;
        }
        this.fields[selectedIndex].getFieldElement().trigger("click");
        $el.blur();
        this.setPlaceholder();
    },
    getPlaceholder: function() {
        // Covers the use case where you have an actiondropdown field on listview right column, and list-column-ellipsis
        // plugin is disabled. Actiondropdown will be rendered empty if viewName equals to list-header.
        if (this.options.viewName === 'list-header') return app.view.Field.prototype.getPlaceholder.call(this);

        var cssClass = [],
            container = '',
            caretClass = this.def.primary ? 'btn btn-primary dropdown-toggle' : 'btn dropdown-toggle',
            caret = '<a class="' + caretClass + '" data-toggle="dropdown" href="javascript:void(0);"><span class="icon-caret-down"></span></a>',
            dropdown = '<ul class="dropdown-menu">';

        if(app.utils.isTouchDevice()) {
            caret += '<select data-toggle="dropdownmenu" class="hide dropdown-menu-select"></select>';
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
                container += field.getPlaceholder();
            } else {
                //first time, unbind the dropdown button fields from the field's list
                //these fields are will be bound once the dropdown toggle is clicked
                delete this.view.fields[field.sfId];
                this.dropdownFields.push(field);

                if(index == 1) {
                    cssClass.push('actions', 'btn-group');
                    container += caret;
                    container += dropdown;
                }
                container += '<li>' + field.getPlaceholder() + '</li>';
            }

        }, this);
        var cssName = cssClass.join(' '),
            placeholder = '<span sfuuid="' + this.sfId + '" class="' + cssName + '">' + container;
        placeholder += (_.size(this.def.buttons) > 0) ? '</ul></span>': '</span>';
        return new Handlebars.SafeString(placeholder);

    },
    _render: function() {
        app.view.invokeParent(this, {type: 'field', name: 'fieldset', method: '_render'});
        this.setPlaceholder();
    },
    setPlaceholder: function() {
        if(this.disposed) {
            return;
        }

        var index = 0,
            //Using document fragment to reduce calculating dom tree
            visibleEl = document.createDocumentFragment(),
            hiddenEl = document.createDocumentFragment(),
            selectEl = this.$(".dropdown-menu-select"),
            html = '<option></option>';
        _.each(this.fields, function(field, idx){
            var cssClass = _.unique(field.def.css_class ? field.def.css_class.split(' ') : []),
                fieldPlaceholder = this.$("span[sfuuid='" + field.sfId + "']");
            if (field.isVisible() && field.hasAccess()) {
                cssClass = _.without(cssClass, 'hide');
                fieldPlaceholder.toggleClass('hide', false);
                if(index == 0) {
                    cssClass.push('btn');
                    field.getFieldElement().addClass("btn");
                    if(this.def.primary) {
                        cssClass.push('btn-primary');
                        field.getFieldElement().addClass("btn-primary");
                    }
                    //The first field needs to be out of the dropdown
                    this.$el.prepend(fieldPlaceholder);
                } else {
                    cssClass = _.without(cssClass, 'btn', 'btn-primary');
                    field.getFieldElement().removeClass("btn btn-primary");
                    //Append field into the dropdown
                    var dropdownEl = document.createElement('li');
                    dropdownEl.appendChild(fieldPlaceholder.get(0));
                    visibleEl.appendChild(dropdownEl);

                    html += '<option value=' + idx + '>' + field.label + '</option>';
                }
                index++;
            } else {
                cssClass.push('hide');
                fieldPlaceholder.toggleClass('hide', true);
                //Drop hidden field out of the dropdown
                hiddenEl.appendChild(fieldPlaceholder.get(0));
            }
            cssClass = _.unique(cssClass);
            field.def.css_class = cssClass.join(' ');
        }, this);

        if(index <= 1) {
            this.$(".dropdown-toggle").hide();
            selectEl.addClass("hide");
            this.$el.removeClass('btn-group');
        } else {
            this.$(".dropdown-toggle").show();
            selectEl.removeClass("hide");
            this.$el.addClass('btn-group');
        }
        //remove all previous built dropdown tree
        this.$(".dropdown-menu").children("li").remove();
        //and then set the dropdown list with new button list set
        this.$(".dropdown-menu").append(visibleEl);
        this.$el.append(hiddenEl);

        if(app.utils.isTouchDevice()) {
            selectEl.html(html);
        }

        //if the first button is hidden due to the acl,
        //it will build all other dropdown button and set it use dropdown button set
        var firstButton = _.first(this.fields);
        if(firstButton && !firstButton.isVisible()) {
            this.renderDropdown();
        }
    },
    setDisabled: function(disable) {
        app.view.invokeParent(this, {type: 'field', name: 'fieldset', method: 'setDisabled', args: [disable]});
        disable = _.isUndefined(disable) ? true : disable;
        if (disable) {
            this.$('.dropdown-toggle').addClass('disabled');
        } else {
            this.$('.dropdown-toggle').removeClass('disabled');
        }
    },

    _dispose: function() {
        _.each(this.fields, function(field) {
            field.off('show hide', this.setPlaceholder, this);
        }, this);
        this.dropdownFields = null;
        app.view.invokeParent(this, {type: 'field', name: 'fieldset', method: '_dispose'});
    },

    /**
     *  Visibility Check
     */
    isVisible: function() {
        return !this.getFieldElement().is(':hidden');
    }
})
