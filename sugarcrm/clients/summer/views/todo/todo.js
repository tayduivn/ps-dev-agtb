({
    events: {
        'click .todo-pills': function(e) { this.pillSwitcher(e, this) },
        'click .todo-container': 'persistMenu',
        'click .todo': 'handleEscKey',
        'click .todo-add': 'todoSubmit',
        'keyup .todo-subject':'todoSubmit',
        'keyup .todo-date':'todoSubmit',
        'focus .todo-date': 'showDatePicker',
        'click .todo-status': 'changeStatus',
        'click .todo-remove': 'removeTodo'
    },
    initialize: function(options) {
        var self = this;
        this.open = false;
        app.view.View.prototype.initialize.call(this, options);
        app.events.on("app:sync:complete", function() {

            self.collection = app.data.createBeanCollection("Tasks");
            self.collection.fetch({myItems: true, success: function(collection) {

                collection.modelList = {
                    today: [],
                    overdue: [],
                    upcoming: []
                };

                _.each(collection.models, function(model) {
                    collection.modelList[self.getTaskType(model.attributes.date_due)].push(model);
                });
                self.overduePillActive = true;
                self.render();
            }});

            self.bindDataChange();
        });
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
        app.events.on("app:view:change", function(layout, obj) {
            if( layout == "record" ) {
                    this.modelID = obj.modelId;
            }
            this.currLayout = layout;
            this.currModule = obj.module;
            this.open = false;
            self.render();
        }, this);

        app.events.on("app:view:todo-list:refresh", function(model, action) {
            var taskType = self.getTaskType(model.attributes.date_due),
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
                self.open = false;
                self.render();
            }
        });
    },
    persistMenu: function(e) {
        // This will prevent the dropup menu from closing
        // when clicking anywhere on it
        e.stopPropagation();
    },
    handleEscKey: function() {
        // todo: change this horribly inefficient code later
        $(document).keyup(function(event) {
            // check if the menu is active
            if( $(".todo-list-widget").hasClass("open") ) {
                // If esc was pressed
                if( event.keyCode == 27 ) {
                    $(".todo-list-widget").removeClass("open");
                }
            }
        });
    },
    showDatePicker: function() {
        this.$(".todo-date").datepicker({
            dateFormat: "yy-mm-dd"
        });
        $("#ui-datepicker-div").css("z-index", 1032);
        $("#ui-datepicker-div").on("click", function(e) {
            e.stopPropagation();
        });
    },
    pillSwitcher: function(e, context) {
        var clickedEl = context.$(e.target);
        var clickedIndex = context.$(".todo-pills").index(clickedEl.closest(".todo-pills"));

        context.$(".todo-pills.active").removeClass("active");
        context.$(".tab-pane.active").removeClass("active");
        clickedEl.closest(".todo-pills").addClass("active");
        context.$(context.$(".tab-pane")[clickedIndex]).addClass("active");

        // this is "state-machine information" that will later get fed into render
        switch(clickedIndex) {
            case 0:
                context.overduePillActive = true;
                context.todayPillActive = false;
                context.upcomingPillActive = false;
                context.allPillActive = false;
                break;
            case 1:
                context.overduePillActive = false;
                context.todayPillActive = true;
                context.upcomingPillActive = false;
                context.allPillActive = false;
                break;
            case 2:
                context.overduePillActive = false;
                context.todayPillActive = false;
                context.upcomingPillActive = true;
                context.allPillActive = false;
                break;
            case 3:
                context.overduePillActive = false;
                context.todayPillActive = false;
                context.upcomingPillActive = false;
                context.allPillActive = true;
                break;
        }
    },
    getModelInfo: function(e) {
        var clickedEl = this.$(e.target).parents(".todo-item-container")[0],
            modelIndex = (this.$(".tab-pane.active").children()).index(clickedEl),
            parentID = this.$(".tab-pane.active").attr("id"),
            record;

        switch(parentID) {
            case "pane1":
                record = this.collection.modelList['overdue'];
                break;
            case "pane2":
                record = this.collection.modelList['today'];
                break;
            case "pane3":
                record = this.collection.modelList['upcoming'];
                break;
            case "pane4":
                var taskType = this.getTaskType(this.collection.models[modelIndex].attributes.date_due);
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

        this.model = this.collection.get(record[modelIndex].id);
        this.model.destroy({ success: function() {
            record.splice(modelIndex, 1);

            self.open = true;
            self.render();
            if( self.model.attributes.parent_id ) {
                app.events.trigger("app:view:todo:refresh", self.model, "delete");
            }
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
        this.open = true;
        this.render();

        // call trigger only if we're updating a related record
        if( this.model.attributes.parent_id ) {
            app.events.trigger("app:view:todo:refresh", this.model, "update_status");
        }
    },
    _renderHtml: function() {
        this.isAuthenticated = app.api.isAuthenticated();
        if (app.config && app.config.logoURL) {
            this.logoURL=app.config.logoURL;
        }
        app.view.View.prototype._renderHtml.call(this);
    },
    _render: function() {
        app.view.View.prototype._render.call(this);
    },
    getTaskType: function(todoDate) {
        var todayBegin = new Date().setHours(0,0,0,0),
            todayEnd   = new Date().setHours(23,59,59,999),
            splitValue = /^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2})\.*\d*([Z+-].*)$/.exec(todoDate),
            todoStamp  = app.date.parse(splitValue[1] + " " + splitValue[2]).getTime();

        // If the task falls in today's range
        if( todoStamp >= todayBegin && todoStamp <= todayEnd ) {
            return "today";
        }
        else if( todoStamp < todayBegin ) {
            return "overdue";
        }
        else {
            return "upcoming";
        }
    },
    validateTodo: function(e) {
        var subject = this.$(".todo-subject"),
            date = this.$(".todo-date"),
            target = this.$(e.target);

        if( subject.val() == "" ) {
            // apply input error class
            subject.parent().addClass("control-group error");
            subject.one("keyup", function() {
                subject.parent().removeClass("control-group error");
            });
        }
        else if( !(app.date.parse(date.val(), app.date.guessFormat(date.val()))) ) {
            // apply input error class
            date.parent().addClass("control-group error");
            date.one("click", function() {
                date.parent().removeClass("control-group error");
            });
        }
        else {
            var datetime = date.val() + "T00:00:00+0000";

            this.model = app.data.createBean("Tasks", {
                "name": subject.val(),
                "assigned_user_id": app.user.get("id"),
                "date_due": datetime
            });

            if( this.$(".todo-related").is(":checked") ) {
                this.model.set({"parent_id": this.modelID});
                this.model.set({"parent_type": this.currModule});
            }

            this.collection.add(this.model);
            this.model.save();
            this.collection.modelList[this.getTaskType(datetime)].push(this.model);

            // only trigger a refresh if the user wants to relate the to-do
            // to the current record
            if( this.model.attributes.parent_id ) {
                app.events.trigger("app:view:todo:refresh", this.model, "create");
            }

            subject.val("");
            date.val("");
            this.open = true;
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
    bindDataChange: function() {
        var self = this;
        if( this.collection ) {
            this.collection.on("reset", function() {
                self.render();
            }, this);
        }
    }
})