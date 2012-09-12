({
    events: {
        'click .todo-pills': function(e) { app.view.views.TodoView.prototype.pillSwitcher(e, this) },
        'focus .todo-date': 'showDatePicker',
        'click .todo-status': 'changeStatus',
        'click .todo-remove': 'removeTodo',
        'click .todo-add': 'todoSubmit',
        'keyup .todo-subject':'todoSubmit',
        'keyup .todo-date':'todoSubmit'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        console.log("-----------");
        console.log("todo-list init");
        console.log(options);
        console.log(this);
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
                        console.log(self.collection.models);

                        self.populateModelList(self.collection);
                        self.overduePillActive = true;
                        self.render();
                    }
                }});
            }
        }, this);

        app.events.on("app:view:todo:refresh", function(model, action) {
            console.log("baa");
            console.log(model);
            console.log(action);
            var taskType = self.app.view.views.TodoView.prototype.getTaskType(model.attributes.date_due),
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
        this.$(".todo-date").datepicker({
            dateFormat: "yy-mm-dd"
        });
    },
    getModelInfo: function(e) {
        var clickedEl = this.$(e.target).parents(".todo-item-container")[0],
            modelIndex = (this.$(".tab-pane.active").children()).index(clickedEl),
            parentID = this.$(".tab-pane.active").attr("id"),
            record;

        switch(parentID) {
            case "todo-overdue":
                record = this.collection.modelList['overdue'];
                break;
            case "todo-today":
                record = this.collection.modelList['today'];
                break;
            case "todo-upcoming":
                record = this.collection.modelList['upcoming'];
                break;
            case "todo-all":
                var taskType = app.view.views.TodoView.prototype.getTaskType(this.collection.models[modelIndex].attributes.date_due);
                record = this.collection.modelList[taskType];
                modelIndex = _.indexOf(_.pluck(record, 'id'), this.collection.models[modelIndex].id);
                break;
        }

        return {index: modelIndex, modList: record};
    },
    removeTodo: function(e) {
        var self = this,
            modelInfo = this.getModelInfo(e),
            modelIndex = modelInfo.index,
            record = modelInfo.modList;
        console.log(modelInfo);

        this.model = this.collection.get(record[modelIndex].id);
        this.model.destroy({ success: function() {
            record.splice(modelIndex, 1);
            self.render();
            app.events.trigger("app:view:todo-list:refresh", self.model, "delete");
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
        app.events.trigger("app:view:todo-list:refresh", this.model, "update_status");
    },
    validateTodo: function(e) {
        var subject = this.$(".todo-subject"),
            date = this.$(".todo-date"),
            target = this.$(e.target);

        if( subject.val() == "" ) {
            // apply input error class
            subject.parent().addClass("control-group error");
            subject.one("keyup", function() {
                target.parent().removeClass("control-group error");
            });
        }
        else if( !(app.date.parse(date.val(), app.date.guessFormat(date.val()))) ) {
            // apply input error class
            date.parent().addClass("control-group error");
            date.one("click", function() {
                target.parent().removeClass("control-group error");
            });
        }
        else {
            var datetime = date.val() + "T00:00:00+0000";

            this.model = app.data.createBean("Tasks", {
                "name": subject.val(),
                "assigned_user_id": app.user.get("id"),
                "date_due": datetime,
                "parent_id": this.modelID,
                "parent_type": this.module
            });

            this.collection.add(this.model);
            this.model.save();
            this.collection.modelList[this.app.view.views.TodoView.prototype.getTaskType(datetime)].push(this.model);
            app.events.trigger("app:view:todo-list:refresh", this.model, "create");

            subject.val("");
            date.val("");
            this.render();
        }
    },
    todoSubmit: function(e) {
        var target = this.$(e.target),
            dateInput = this.$(".todo-date-container");

        if( target.hasClass("todo-subject") || target.hasClass("todo-date") ) {
            // show the date-picker input field
            if( !(dateInput.is(":visible")) ) {
                dateInput.css("display", "inline-block");
            }
            // if enter was pressed
            if( e.keyCode == 13 ) {
                // validate
                this.validateTodo(e);
            }
        }
        else {
            // Add button was clicked
            this.validateTodo(e);
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
            collection.modelList[self.app.view.views.TodoView.prototype.getTaskType(model.attributes.date_due)].push(model);
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