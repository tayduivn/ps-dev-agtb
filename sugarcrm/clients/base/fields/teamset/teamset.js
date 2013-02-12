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
    extendsFrom: 'RelateField',
    minChars: 1,
    allow_single_deselect: false,
    events: {
        'click .btn[name=add]' : 'addItem',
        'click .btn[name=remove]' : 'removeItem',
        'click .btn[name=primary]' : 'setPrimaryItem'
    },
    _render: function() {
        var result = app.view.fields.RelateField.prototype._render.call(this),
            self = this;
        if(this.tplName === 'edit') {
            this.$(this.fieldTag).each(function(index, el){
                var plugin = $(el).data("select2");
                if(!plugin.setTeamIndex) {
                    plugin.setTeamIndex = function() {
                        self.currentIndex = $(this).data("index");
                    }
                    plugin.opts.element.on("open", plugin.setTeamIndex);
                }
            });
        }
    },
    setValue: function(model) {
        var index = _.isUndefined(this.currentIndex) ? this.$(".chzn-container-active").prev().data('index') : this.currentIndex,
            team = this.value,
            silent = model.silent || false;
        team[index || 0].id = model.id;
        team[index || 0].name = model.value;
        this.model.set(this.def.name, team, {silent: true});
        //Since team is an array form, onchange backbone triggers only the team_name array is added or removed.
        //Replacing among the list item should call onchange function.
        if(!silent) {
            this.model.trigger("change:" + this.def.name);
        }
    },
    format: function(value) {
        if(this.model.isNew()) {
            //load the default team setting that is specified in the user profile settings
            value = value || app.user.getPreference("default_teams");
            this.model.set(this.name, value);
        }

        if(_.isArray(value)) {
            value = _.sortBy(value, function(team) {
                delete team.add_button;
                return team.primary ? -1 : 0;
            });
        } else {
            value = [
                {name: value}
            ];
        }
        value[value.length - 1].add_button = true;
        if(this.tplName === 'list' && _.isArray(value)) {
            value = value[0].name;
        }
        return value;
    },
    addTeam: function() {
        this.value.push({});
        this.model.set(this.def.name, this.value, {silent: true});
        this.model.trigger("change:" + this.def.name);
    },
    removeTeam: function(index) {
        if(index === 0 && this.value.length === 1) {
            this.value[index] = {};
        } else {
            this.value.splice(index, 1);
        }
        this.model.set(this.def.name, this.value, {silent: true});
        this.model.trigger("change:" + this.def.name);
    },
    setPrimary: function(index) {
        _.each(this.value, function(team, i) {
            team.primary = (i == index) ? true : false;
        });
        this.model.set(this.def.name, this.value, {silent: true});
    },
    addItem: _.debounce(function(evt) {
        this.addTeam();
    }, 0),
    removeItem: _.debounce(function(evt) {
        var index = $(evt.currentTarget).data('index');
        this.removeTeam(index);
    }, 0),
    setPrimaryItem: _.debounce(function(evt) {
        var index = $(evt.currentTarget).data('index');
        this.setPrimary(index);
        this.$(".btn[name=primary]").removeClass("active");
        this.$(".btn[name=primary][data-index=" + index + "]").addClass("active");
    }, 0),
    bindDomChange: function() {
        //To avoid re-render on change the field
    },
    getFieldElement: function() {
        return this.$el;
    }
    //TODO: Handle validation error
})
