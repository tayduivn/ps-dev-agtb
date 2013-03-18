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
        'click .btn[name=primary]' : 'setPrimaryItem',
        'change input.select2': 'inputChanged'
    },
    initialize: function(options){
        app.view.fields.RelateField.prototype.initialize.call(this, options);
        //Moving primary teams to top on init
        this.model.set(
            this.name,
            _.sortBy(this.model.get(this.name), function(team) {
                return team.primary ? -1 : 0;
            })
        );
    },
    _render: function() {
        var self = this;
        app.view.fields.RelateField.prototype._render.call(this);
        if(this.tplName === 'edit') {
            this.$(this.fieldTag).each(function(index, el){
                var plugin = $(el).data("select2");
                if(!plugin.setTeamIndex) {
                    plugin.setTeamIndex = function() {
                        self.currentIndex = $(this).data("index");
                    };
                    plugin.opts.element.on("open", plugin.setTeamIndex);
                }
            });
        }
    },
    /**
     * Called to update value when a selection is made from options view dialog
     * @param model New value for teamset
     */
    setValue: function(model) {
        var index = this.currentIndex,
            team = this.value;
        team[index || 0].id = model.id;
        team[index || 0].name = model.value;
        this._updateAndTriggerChange(team);
    },
    format: function(value) {
        if(this.model.isNew()) {
            //load the default team setting that is specified in the user profile settings
            if(_.isEmpty(value)){
                value = app.user.getPreference("default_teams");
            }
            this.model.set(this.name, value);
        }
        if(this.tplName === 'list') {
            return _.isArray(value) ? value[0].name : value;
        }
        if(!_.isArray(value)) {
            value = [
                {name: value}
            ];
        }
        // Place the add button as needed
        if(_.isArray(value) && value.length > 0){
            _.each(value, function(team){
                delete team.remove_button;
                delete team.add_button;
            });
            value[value.length - 1].add_button = true;
            // Count the number of valid teams
            var numTeams = 0;
            _.each(value, function(team){
                if(!_.isUndefined(team.id)) numTeams++;
            });
            // Show remove button for all unset combos and only set combos if there are more than one
            _.each(value, function(team){
                if(_.isUndefined(team.id) || numTeams > 1){
                    team.remove_button = true;
                }
            });
        }
        return value;
    },
    addTeam: function() {
        this.value.push({});
        this._updateAndTriggerChange(this.value);
    },
    removeTeam: function(index) {
        // Do not remove last team.
        if(index === 0 && this.value.length === 1) {
            return;
        } else {
            //Pick first team to be Primary if we're removing Primary team
            var removed = this.value.splice(index, 1);
            if(removed && removed.length > 0 && removed[0].primary){
                this.setPrimary(0);
            }
        }
        this._updateAndTriggerChange(this.value);
    },
    setPrimary: function(index) {
        _.each(this.value, function(team, i) {
            team.primary = false;
        });
        //If this team is set, then allow it to turn primary
        if(this.value[index].name){
            this.value[index].primary = true;
        }
        this._updateAndTriggerChange(this.value);
        return (this.value[index]) ? this.value[index].primary : false;
    },
    //Forcing change event since backbone isn't picking up on changes within an object within the array.
    inputChanged: function(evt){
        this._updateAndTriggerChange(this.value);
    },
    /**
     * Forcing change event on value update since backbone isn't picking up on changes within an object within the array.
     * @param value New value for teamset field
     * @private
     */
    _updateAndTriggerChange: function(value){
        this.model.set(this.name, value, {silent: true});
        this.model.trigger("change:"+this.name);
        this.model.trigger("change");
    },
    addItem: _.debounce(function(evt) {
        this.addTeam();
    }, 0),
    removeItem: _.debounce(function(evt) {
        var index = $(evt.currentTarget).data('index');
        if(_.isNumber(index)){
            this.removeTeam(index);
        }
    }, 0),
    setPrimaryItem: _.debounce(function(evt) {
        var index = $(evt.currentTarget).data('index');

        //Don't allow setting to primary until user's selected an actual team (SP-530)
        if (! this.value[index].id ) {
            return;
        }
        this.$(".btn[name=primary]").removeClass("active");
        if(this.setPrimary(index)){
            this.$(".btn[name=primary][data-index=" + index + "]").addClass("active");
        }
    }, 0)
})
