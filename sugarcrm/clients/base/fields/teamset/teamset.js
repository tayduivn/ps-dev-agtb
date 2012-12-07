({
    extendsFrom: 'RelateField',
    minChars: 1,
    allow_single_deselect: false,
    events: {
        'keyup .chzn-search input': 'throttleSearch',
        'click .btn[name=add]' : 'addItem',
        'click .btn[name=remove]' : 'removeItem',
        'click .btn[name=primary]' : 'setPrimaryItem'
    },
    setValue: function(model) {
        var index = this.$(".chzn-container-active").prev().data('index'),
            team = this.value;
        team[index].id = model.id;
        team[index].name = model.value;
        this.model.set(this.def.name, team);
    },
    format: function(value) {
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
        this.model.set(this.def.name, this.value);
    },
    removeTeam: function(index) {
        this.value.splice(index, 1);
        this.model.set(this.def.name, this.value);
    },
    setPrimary: function(index) {
        _.each(this.value, function(team, i) {
            team.primary = (i == index) ? true : false;
        });
        this.model.set(this.def.name, this.value);
    },
    addItem: function(evt) {
        this.addTeam();
        this.render();
    },
    removeItem: function(evt) {
        var index = $(evt.currentTarget).data('index');
        this.removeTeam(index);
        this.render();
    },
    setPrimaryItem: function(evt) {
        var index = $(evt.currentTarget).data('index');
        this.setPrimary(index);
        this.$(".btn[name=primary]").removeClass("active");
        this.$(".btn[name=primary][data-index=" + index + "]").addClass("active");
    },
    throttleSearch: function(evt) {
        this.$(this.fieldTag).attr("disabled", true);
        this.$(".chzn-container-active").prev().attr("disabled" , false);
        this._previousTerm = '';
        app.view.fields.RelateField.prototype.throttleSearch.call(this, evt);
    },
    bindDomChange: function() {
        //To avoid re-render on change the field
    }
    //TODO: Handle validation error
})