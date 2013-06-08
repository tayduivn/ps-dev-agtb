({
    events: {
        'click .checkall': 'checkAll',
        'click input[name="check"]': 'check',
        'change [data-toggle=dropdownmenu]' : 'dropdownSelected'
    },
    fields: null, //action button fields
    actionDropDownTag: ".dropdown-toggle",
    fieldTag: "input[name=check]",
    initialize: function (options) {
        app.view.Field.prototype.initialize.call(this, options);
        var massCollection = this.context.get('mass_collection');
        if (!massCollection) {
            var MassCollection = app.BeanCollection.extend({
                reset: function () {
                    this.filterDef = null;
                    this.entire = false;
                    Backbone.Collection.prototype.reset.call(this);
                }
            });
            massCollection = new MassCollection();
            this.context.set('mass_collection', massCollection);
        }
    },
    check: function (evt) {
        this.toggleSelect(this.$(this.fieldTag).is(":checked"));
    },
    checkAll: function (evt) {
        var checkbox = this.$(this.fieldTag);

        if (checkbox && evt.currentTarget == evt.target) {
            checkbox.attr("checked", !checkbox.is(":checked"));
        }
        this.toggleSelect(checkbox.is(":checked"));
    },
    dropdownSelected: function(evt) {
        var $el = this.$(evt.currentTarget),
            selectedIndex = $el.val();
        if(!selectedIndex) {
            return;
        }
        this.fields[selectedIndex].getFieldElement().trigger("click");
        $el.blur();
        evt.currentTarget.selectedIndex = 0;
    },
    toggleSelect: function (check) {
        var massCollection = this.context.get('mass_collection');
        if (massCollection) {
            if (check) { //if checkbox is selected
                if (this.model.id) { //each selection
                    massCollection.add(this.model);
                } else {
                    //entire selection
                    massCollection.reset();
                    massCollection.add(this.view.collection.models);
                    massCollection.filterDef = this.view.collection.filterDef;
                }
            } else { //if checkbox is unchecked
                if (this.model.id) { //each selection
                    if (massCollection.entire) {
                        massCollection.reset();
                        massCollection.add(this.view.collection.models);
                        massCollection.remove(this.model);
                    } else {
                        massCollection.remove(this.model);
                    }
                } else { //entire selection
                    massCollection.reset();
                }
            }
        }
    },
    bindDataChange: function () {
        var self = this,
            massCollection = this.context.get('mass_collection');
        if (massCollection && this.model.id) { //listeners for each record selection
            var modelId = this.model.id;

            massCollection.off("add", null, modelId);
            massCollection.off("remove", null, modelId);
            massCollection.off("reset", null, modelId);

            massCollection.on("add", function (model) {
                if (self.model && model.id == self.model.id) {
                    self.$(self.fieldTag).attr("checked", true);
                }
            }, modelId);
            massCollection.on("remove", function (model) {
                if (self.model && model.id == self.model.id) {
                    self.$(self.fieldTag).attr("checked", false);
                }
            }, modelId);
            massCollection.on("reset", function () {
                self.$(self.fieldTag).attr("checked", false);
            }, modelId);
            if (massCollection.get(this.model) || massCollection.entire) {
                this.$(self.fieldTag).attr("checked", true);
                this.selected = true;
            } else {
                delete this.selected;
            }
        } else if (massCollection) { //listeners for entire selection
            var cid = this.view.cid;
            massCollection.off("add", null, cid);
            massCollection.off("remove", null, cid);
            massCollection.off("reset", null, cid);


            var setButtonsDisabled = function (fields) {
                _.each(fields, function (field) {
                    if (field.def.minSelection || field.def.maxSelection) {
                        var min = field.def.minSelection || 0,
                            max = field.def.maxSelection || massCollection.length;
                        if (massCollection.length < min || massCollection.length > max) {
                            field.setDisabled(true);
                        } else {
                            field.setDisabled(false);
                        }
                    }
                }, self);
            };
            if (this.view.collection) {
                this.view.collection.off("reset", null, this);
                this.view.collection.on("reset", function () {
                    if (massCollection.entire) {
                        massCollection.reset();
                    }
                }, this);
            }

            this.off("render", null, this);
            this.on("render", this.toggleShowSelectAll, this);

            massCollection.on("add", function (model) {
                if (massCollection.length > 0) {
                    self.$(self.actionDropDownTag).removeClass("disabled");
                    self.$(".dropdown-menu-select").removeClass("hide");
                }
                if (massCollection.length == self.view.collection.length) {
                    self.$(self.fieldTag).attr("checked", true);
                }
                self.toggleShowSelectAll();
                setButtonsDisabled(self.fields);
            }, cid);
            massCollection.on("remove reset", function (model) {
                if (massCollection.length == 0) {
                    self.$(self.actionDropDownTag).addClass("disabled");
                    self.$(".dropdown-menu-select").addClass("hide");
                }
                self.$(self.fieldTag).attr("checked", false);
                self.toggleShowSelectAll();
                setButtonsDisabled(self.fields);
            }, cid);
            this.action_enabled = (massCollection.length > 0);
            this.selected = (massCollection.entire);
        }
    },
    toggleShowSelectAll: function () {
        var massCollection = (this.context) ? this.context.get('mass_collection') : null;
        if (massCollection && this.view.collection.next_offset > 0) {
            //only if the collection contains more records
            var self = this;
            if (massCollection.entire) {
                var allSelected = $('<div>').html(app.lang.get('LBL_LISTVIEW_SELECTED_ALL'));
                $(allSelected).find('a').on("click", function (evt) {
                    massCollection.reset();
                });
                this.view.layout.trigger("list:alert:show", allSelected);
            } else if (massCollection.length == this.view.collection.models.length) {
                var selectAll = $("<div>").html(app.utils.formatString(
                    app.lang.get('LBL_LISTVIEW_SELECT_ALL_RECORDS'), {
                        "num": massCollection.length
                    }));
                $(selectAll).find('a').on("click", function (evt) {
                    massCollection.entire = true;
                    self.toggleShowSelectAll();
                });
                this.view.layout.trigger("list:alert:show", selectAll);
            } else {
                this.view.layout.trigger("list:alert:hide");
            }
        }
    },
    getPlaceholder: function () {
        var self = this,
            viewName = this.options.viewName || this.view.name;

        if (!this.fields && viewName == 'list-header') {
            this.fields = [];
            var actionMenu = '<ul class="dropdown-menu">';
            _.each(this.def.buttons, function (fieldDef) {
                var field = app.view.createField({
                    def: fieldDef,
                    view: self.view,
                    viewName: self.options.viewName,
                    model: self.model
                });
                field.on("show hide", self.setPlaceholder, self);
                self.fields.push(field);
                field.parent = self;
                actionMenu += '<li>' + field.getPlaceholder() + '</li>';

            });
            actionMenu += "</ul>";
            var caret = '';
            if(app.utils.isTouchDevice()) {
                caret += '<select data-toggle="dropdownmenu" class="hide dropdown-menu-select"></select>';
            }
            self.actionPlaceHolder = new Handlebars.SafeString(caret + actionMenu);
        }
        return app.view.Field.prototype.getPlaceholder.call(this);
    },
    _loadTemplate: function () {
        app.view.Field.prototype._loadTemplate.call(this);
        if (this.view.action === 'list' && this.action === 'edit') {
            this.template = app.template.empty;
        }
    },
    setPlaceholder: function () {
        var index = 0,
            selectEl = this.$(".dropdown-menu-select"),
            html = '<option></option>';

        _.each(this.fields, function (field, idx) {
            var fieldPlaceholder = this.$("span[sfuuid='" + field.sfId + "']");
            if (!field.isVisible()) {
                fieldPlaceholder.toggleClass('hide', true);
                this.$el.append(fieldPlaceholder);
            } else {
                fieldPlaceholder.toggleClass('hide', false);
                this.$(".dropdown-menu").append($('<li>').append(fieldPlaceholder));
                html += '<option value=' + idx + '>' + field.label + '</option>';
                index++;
            }
        }, this);


        if (index < 1) {
            this.$(".dropdown-toggle").hide();
        } else {
            this.$(".dropdown-toggle").show();
        }
        this.$(".dropdown-menu").children("li").each(function (index, el) {
            if ($(el).html() === '') {
                $(el).remove();
            }
        });
        if(app.utils.isTouchDevice()) {
            selectEl.html(html);
        }
    },
    unbindData: function() {
        var collection = this.context.get('mass_collection');
        if(collection) {
            collection.off();
        }
        if(this.view.collection) {
            this.view.collection.off("reset", null, this);
        }
        app.view.Field.prototype.unbindData.call(this);
    },
    _dispose: function() {
        _.each(this.fields, function(field) {
            field.parent = null;
            field.dispose();
        });
        this.fields = null;
        app.view.Field.prototype._dispose.call(this);
    },
    /**
     * {@inheritdoc}
     *
     * No data changes to bind.
     */
    bindDomChange: function () {
    },
    /**
     * {@inheritdoc}
     *
     * No need to unbind DOM changes to a model.
     */
    unbindDom: function () {
    }
})
