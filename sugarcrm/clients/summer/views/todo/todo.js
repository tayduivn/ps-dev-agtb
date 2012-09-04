({
    events: {
        'click #todo-pills li a': 'pillSwitcher',
        'click #todo-container': 'persistMenu',
        'click #todo': 'handleEscKey',
        'click #todo-add': 'todoSubmit',
        'keyup #todo-subject':'todoSubmit',
        'keyup #todo-date':'todoSubmit',
        'focus #todo-date': 'showDatePicker',
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
    },
    persistMenu: function(e) {
        // This will prevent the dropup menu from closing
        // when clicking anywhere on it
        e.stopPropagation();
    },
    handleEscKey: function() {
        $(document).keyup(function(event) {
            // check if the menu is active
            if( $("#todo-list-widget").hasClass("btn-group dropup open") ) {
                // If esc was pressed
                if( event.keyCode == 27 ) {
                    $("#todo-list-widget").attr("class", "btn-group dropup");
                }
            }
        });
    },
    showDatePicker: function() {
        $("#todo-date").datepicker({
            dateFormat: "yy-mm-dd"
        });
        $("#ui-datepicker-div").css("z-index", 1032);
        $("#ui-datepicker-div").on("click", function(e) {
            e.stopPropagation();
        });
    },
    checkActivePill: function() {
        if( $("#todo-pill-overdue").hasClass("active") ) {
            this.overduePillActive = true;
            this.todayPillActive = false;
            this.upcomingPillActive = false;
            this.allPillActive = false;
        }
        else if( $("#todo-pill-today").hasClass("active") ) {
            this.overduePillActive = false;
            this.todayPillActive = true;
            this.upcomingPillActive = false;
            this.allPillActive = false;
        }
        else if( $("#todo-pill-upcoming").hasClass("active") ) {
            this.overduePillActive = false;
            this.todayPillActive = false;
            this.upcomingPillActive = true;
            this.allPillActive = false;
        }
        else {
            this.overduePillActive = false;
            this.todayPillActive = false;
            this.upcomingPillActive = false;
            this.allPillActive = true;
        }
    },
    pillSwitcher: function(e) {
        var clickedIndex = $("#todo-pills li").index($(e.target).closest("[id^='todo-pill']")[0]);

        $("#todo-pills li.active").removeClass("active");
        $(".tab-pane.active").removeClass("active");
        $(e.target).closest("[id^='todo-pill']").addClass("active");
        $($(".tab-pane")[clickedIndex]).addClass("active");
    },
    getModelInfo: function(e) {
        var clickedEl = $(e.target).parents(".todo-item-container")[0],
            modelIndex = ($(".tab-pane.active").children()).index(clickedEl),
            parentID = $(".tab-pane.active").attr("id"),
            record;

        // hipsters use switch-cases, true story
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
            self.checkActivePill();
            self.render();
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
        this.checkActivePill();
        this.render();
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
            todoStamp  = app.date.parse(todoDate).getTime();

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
        var subject = $("#todo-subject").val(),
            date = $("#todo-date").val();

        if( subject == "" ) {
            // apply input error class
            $("#todo-subject").parent().addClass("control-group error");
            $("#todo-subject").one("keyup", function(e) {
                $(e.target).parent().removeClass("control-group error");
            });
        }
        else if( !(app.date.parse(date, app.date.guessFormat(date))) ) {
            // apply input error class
            $("#todo-date").parent().addClass("control-group error");
            $("#todo-date").one("click", function(e) {
                $(e.target).parent().removeClass("control-group error");
            });
        }
        else {
            var datetime = date + " 00:00:00";

            this.model = app.data.createBean("Tasks", {
                "name": subject,
                "assigned_user_id": app.user.get("id"),
                "date_due": datetime
            });
            this.collection.add(this.model);
            this.model.save();
            this.collection.modelList[this.getTaskType(datetime)].push(this.model);

            $("#todo-subject").val("");
            $("#todo-date").val("");
            this.open = true;
            this.checkActivePill();
            this.render();
        }
    },
    todoSubmit: function(e) {
        if( e.target.id == "todo-subject" || e.target.id == "todo-date" ) {

            // show the date-picker input field
            if( $("#todo-date-container").css("display") == "none" ) {
                $("#todo-date-container").css("display", "inline-block");
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
        if (this.collection) {
            this.collection.on("reset", function() {
                self.render();
            }, this);
        }
    }
})