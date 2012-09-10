({
    events: {
        'click .todo-pills': 'pillSwitcher',
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
    },
    persistMenu: function(e) {
        // This will prevent the dropup menu from closing
        // when clicking anywhere on it
        e.stopPropagation();
    },
    handleEscKey: function() {
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
    pillSwitcher: function(e) {
        var clickedEl = this.$(e.target);
        var clickedIndex = this.$(".todo-pills").index(clickedEl.closest(".todo-pills"));

        this.$(".todo-pills.active").removeClass("active");
        this.$(".tab-pane.active").removeClass("active");
        clickedEl.closest(".todo-pills").addClass("active");
        this.$(this.$(".tab-pane")[clickedIndex]).addClass("active");

        // this is "state-machine information" that will later get fed into render
        switch(clickedIndex) {
            case 0:
                this.overduePillActive = true;
                this.todayPillActive = false;
                this.upcomingPillActive = false;
                this.allPillActive = false;
                break;
            case 1:
                this.overduePillActive = false;
                this.todayPillActive = true;
                this.upcomingPillActive = false;
                this.allPillActive = false;
                break;
            case 2:
                this.overduePillActive = false;
                this.todayPillActive = false;
                this.upcomingPillActive = true;
                this.allPillActive = false;
                break;
            case 3:
                this.overduePillActive = false;
                this.todayPillActive = false;
                this.upcomingPillActive = false;
                this.allPillActive = true;
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
                "date_due": datetime
            });
            this.collection.add(this.model);
            this.model.save();
            this.collection.modelList[this.getTaskType(datetime)].push(this.model);

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
        if (this.collection) {
            this.collection.on("reset", function() {
                self.render();
            }, this);
        }
    }
})