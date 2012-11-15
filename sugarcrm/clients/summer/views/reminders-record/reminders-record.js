({
    events: {
        'click .reminders-pills': function(e) { app.view.views.RemindersView.prototype.pillSwitcher(e, this) },
        'focus .reminders-date': 'showDatePicker',
        'click .reminders-status': 'changeStatus',
        'click .reminders-remove': 'removeReminder',
        'click .reminders-add': 'submitReminder',
        'keyup .reminders-subject':'submitReminder',
        'keyup .reminders-date':'submitReminder'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        var self = this;

        app.events.on("app:view:change", function(layout, obj) {
            if( layout == "record" ) {
                this.modelID = obj.modelId;
                self.collection = app.data.createBeanCollection("Tasks");
                self.collection.fetch({success: function(collection){
                    if( self.collection ) {
                        self.collection.models = _.filter(collection.models, function(model){
                            return (model.attributes.parent_id == self.modelID &&
                                    model.attributes.parent_type == self.module &&
                                    model.attributes.assigned_user_id == app.user.get("id"));
                        });

                        self.populateModelList(self.collection);
                        self.overduePillActive = true;
                        self.render();
                    }
                }});
            }
        }, this);

        app.events.on("app:view:reminders:refresh", function(model, action) {
            var taskType = app.view.views.RemindersView.prototype.getTaskType(model.attributes.date_due),
                givenModel = model;

            if( self.collection ) {
                switch(action) {
                    case "create":
                        self.collection.add(givenModel);
                        self.collection.modelList[taskType].push(givenModel);
                        break;
                    case "update_status":
                        var record = self.collection.modelList[taskType];

                        self.model = self.collection.get(givenModel.id);
                        self.model.attributes.status = givenModel.attributes.status;
                        var listModel = _.find(record, function(param) {
                            return (givenModel.id == param.id);
                        });
                        listModel.attributes.status = givenModel.attributes.status;
                        break;
                    case "delete":
                        var record = self.collection.modelList[taskType],
                            listModel = _.find(record, function(param) {
                                return (givenModel.id == param.id);
                            }),
                            listModelIndex = _.indexOf(record, listModel);

                        self.collection.remove(givenModel, {silent: true});
                        record.splice(listModelIndex, 1);
                        break;
                }
                self.render();
            }
        });
    },
    showDatePicker: function() {
        this.$(".reminders-date").datepicker({
            dateFormat: "yy-mm-dd"
        });
    },
    getModelInfo: function(e) {
        var clickedEl = this.$(e.target).parents(".reminders-item-container")[0],
            modelIndex = (this.$(".tab-pane.active").children()).index(clickedEl),
            parentID = this.$(".tab-pane.active").attr("id"),
            record;

        switch(parentID) {
            case "reminders-overdue":
                record = this.collection.modelList['overdue'];
                break;
            case "reminders-today":
                record = this.collection.modelList['today'];
                break;
            case "reminders-upcoming":
                record = this.collection.modelList['upcoming'];
                break;
            case "reminders-all":
                var taskType = app.view.views.RemindersView.prototype.getTaskType(this.collection.models[modelIndex].attributes.date_due);
                record = this.collection.modelList[taskType];
                modelIndex = _.indexOf(_.pluck(record, 'id'), this.collection.models[modelIndex].id);
                break;
        }

        return {index: modelIndex, modList: record};
    },
    removeReminder: function(e) {
        var self = this,
            modelInfo = this.getModelInfo(e),
            modelIndex = modelInfo.index,
            record = modelInfo.modList;

        this.model = this.collection.get(record[modelIndex].id);
        this.model.destroy({ success: function() {
            record.splice(modelIndex, 1);
            self.render();
            app.events.trigger("app:view:reminders-record:refresh", self.model, "delete");
        }});
    },
    changeStatus: function(e) {
        var modelInfo = this.getModelInfo(e),
            modelIndex = modelInfo.index,
            record = modelInfo.modList,
            taskStatusListStrings = app.lang.getAppListStrings('task_status_dom');

        this.model = this.collection.get(record[modelIndex].id);

        if( this.model.attributes.status == taskStatusListStrings['Completed'] ) {
            this.model.set({
                "status": taskStatusListStrings['Not Started']
            });
            record[modelIndex].attributes.status = taskStatusListStrings['Not Started'];
        }
        else {
            this.model.set({
                "status": taskStatusListStrings['Completed']
            });
            record[modelIndex].attributes.status = taskStatusListStrings['Completed'];
        }

        this.model.save();
        this.render();
        app.events.trigger("app:view:reminders-record:refresh", this.model, "update_status");
    },
    validateReminder: function(e) {
        var subjectEl = this.$(".reminders-subject"),
            subjectVal = subjectEl.val(),
            dateEl = this.$(".reminders-date"),
            dateVal = dateEl.val(),
            dateObj = app.date.parse(dateVal, app.date.guessFormat(dateVal));

        if( subjectVal == "" ) {
            // apply input error class
            subjectEl.parent().addClass("control-group error");
            subjectEl.one("keyup", function() {
                subjectEl.parent().removeClass("control-group error");
            });
        }
        else if( dateObj == "Invalid Date" || !(dateObj) ) {
            // apply input error class
            dateEl.parent().addClass("control-group error");
            dateEl.one("focus", function() {
                dateEl.parent().removeClass("control-group error");
            });
        }
        else {
            var datetime = dateVal + "T00:00:00+0000";

            this.model = app.data.createBean("Tasks", {
                "name": subjectVal,
                "assigned_user_id": app.user.get("id"),
                "date_due": datetime,
                "parent_id": this.modelID,
                "parent_type": this.module
            });

            this.collection.add(this.model);
            this.model.save();
            this.collection.modelList[app.view.views.RemindersView.prototype.getTaskType(datetime)].push(this.model);
            app.events.trigger("app:view:reminders-record:refresh", this.model, "create");

            subjectEl.val("");
            dateEl.val("");
            this.render();
        }
    },
    submitReminder: function(e) {
        var target = this.$(e.target),
            dateInput = this.$(".reminders-date-container");

        if( target.hasClass("reminders-subject") || target.hasClass("reminders-date") ) {
            // show the date-picker input field
            if( !(dateInput.is(":visible")) ) {
                dateInput.css("display", "inline-block");
            }
            // if enter was pressed
            if( e.keyCode == 13 ) {
                // validate
                this.validateReminder(e);
            }
        }
        else {
            // Add button was clicked
            this.validateReminder(e);
        }
    },
    populateModelList: function(collection) {
        var self = this;
        collection.modelList = {
            today: [],
            overdue: [],
            upcoming: []
        };
        _.each(collection.models, function(model) {
            collection.modelList[app.view.views.RemindersView.prototype.getTaskType(model.attributes.date_due)].push(model);
        });
    },
    bindDataChange: function() {
        var self = this;
        if( this.collection ) {
            this.collection.on("reset", function() {
                self.render();
            }, this);
        }
    }
})