({
    events: {
        'click [name="checkall"]': 'checkAll',
        'click input[name="check"]': 'check'
    },
    fields: null, //action button fields
    actionDropDownTag: ".dropdown-toggle",
    fieldTag: "input[name=check]",
    initialize: function(options) {
        var result = app.view.Field.prototype.initialize.call(this, options);
        this.massModel = this.context.get('mass_model');
        if(!this.massModel) {
            var MassModel = app.BeanCollection.extend({
                    reset: function() {
                        this.entire = false;
                        Backbone.Collection.prototype.reset.call(this);
                    }
                });
            this.massModel = new MassModel();
            this.context.set('mass_model', this.massModel);
        }
        return result;
    },
    check: function(evt) {
        this.toggleSelect(this.$(this.fieldTag).is(":checked"));
    },
    checkAll: function(evt) {
        var checkbox = this.$(this.fieldTag);

        if(checkbox && evt.currentTarget == evt.target) {
            checkbox.attr("checked", !checkbox.is(":checked"));
        }
        this.toggleSelect(checkbox.is(":checked"));
    },
    toggleSelect: function(check) {
        if(this.massModel) {
            if(check) { //if checkbox is selected
                if(this.model.id) { //each selection
                    this.massModel.add(this.model);
                } else {
                    //entire selection
                    this.massModel.add(this.view.collection.models);
                }
            } else { //if checkbox is unchecked
                if(this.model.id) { //each selection
                    if(this.massModel.entire) {
                        this.massModel.reset();
                        this.massModel.add(this.view.collection.models);
                        this.massModel.remove(this.model);
                    } else {
                        this.massModel.remove(this.model);
                    }
                } else { //entire selection
                    this.massModel.reset();
                }
            }
        }
    },

    bindDataChange: function() {
        var self = this;
        if (this.massModel && this.model.id) { //listeners for each record selection
            this.massModel.on("add", function(model) {
                if(model.id == self.model.id) {
                    self.$(self.fieldTag).attr("checked", true);
                }
            }, this);
            this.massModel.on("remove", function(model){
                if(model.id == self.model.id) {
                    self.$(self.fieldTag).attr("checked", false);
                }
            });
            this.massModel.on("reset", function(){
                self.$(self.fieldTag).attr("checked", false);
            });
            if(this.massModel.get(this.model) || this.massModel.entire) {
                this.$(self.fieldTag).attr("checked", true);
                this.selected = true;
            } else {
                delete this.selected;
            }
        } else if (this.massModel) { //listeners for entire selection
            this.massModel.on("add", function(model) {
                if(this.length > 0) {
                    self.$(self.actionDropDownTag).removeClass("disabled");
                }
                if(this.length == self.view.collection.length) {
                    self.$(self.fieldTag).attr("checked", true);
                }
                self.toggleShowSelectAll();
            });
            this.massModel.on("remove reset", function(model) {
                if(this.length == 0) {
                    self.$(self.actionDropDownTag).addClass("disabled");
                }
                self.$(self.fieldTag).attr("checked", false);
                self.toggleShowSelectAll();
            });
            this.action_enabled = (this.massModel.length > 0);
            this.selected = (this.massModel.entire);
        }
    },
    toggleShowSelectAll: function() {
        if(this.view.collection.next_offset > 0) {
            //only if the collection contains more records
            var self = this;
            if(this.massModel.entire) {
                var selectAll = $("<a>").attr("href", "javascript:void(0);").html(app.lang.get('LBL_LISTVIEW_CLEAR_ALL')),
                    message = $("<div>").css("textAlign", "center").html(app.lang.get('LBL_LISTVIEW_SELECTED_ALL')).append(selectAll);
                selectAll.on("click", function(evt) {
                    self.massModel.reset();
                });
                this.view.layout.trigger("list:alert:show", message);
            } else if(this.massModel.length == this.view.collection.models.length) {
                var selectAll = $("<a>").attr("href", "javascript:void(0);").html(app.lang.get('LBL_LISTVIEW_OPTION_ENTIRE')),
                    message = $("<div>").css("textAlign", "center").html(app.lang.get('LBL_LISTVIEW_SELECTED_NUM').replace("{num}", this.massModel.length)).append(selectAll).append(app.lang.get('LBL_LISTVIEW_RECORDS'));
                selectAll.on("click", function(evt) {
                    self.massModel.entire = true;
                    self.toggleShowSelectAll();
                });
                this.view.layout.trigger("list:alert:show", message);
            } else {
                this.view.layout.trigger("list:alert:hide");
            }
        }
    },
    getPlaceholder : function(){
        var ret = app.view.Field.prototype.getPlaceholder.call(this);
        var self = this;

        if (!this.fields && this.options.viewName == 'list-header'){
            this.fields = [];
            var actionMenu = '<ul class="dropdown-menu">';
            _.each(this.def.buttons, function(fieldDef){
                var field = app.view.createField({
                    def: fieldDef,
                    view: self.view,
                    viewName: self.options.viewName,
                    model: self.model
                });
                self.fields.push(field);
                actionMenu += '<li>' + field.getPlaceholder() + '</li>';

            });
            actionMenu += "</ul>";
            self.actionPlaceHolder = new Handlebars.SafeString(actionMenu);
        }
        return new Handlebars.SafeString(ret);
    }
})