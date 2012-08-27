({
    events: {
        'click #todo-container': 'onClickNotification',
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

                collection.modelList = {};
                collection.modelList['today'] = [];
                collection.modelList['overdue'] = [];
                collection.modelList['upcoming'] = [];

                for( var modelIndex in collection.models ) {
                    var result = self.getTaskType(collection.models[modelIndex].attributes.date_due);
                    collection.models[modelIndex].attributes.task_type = result;
                    collection.modelList[result].push(collection.models[modelIndex]);
                }
            }});

            self.bindDataChange();
        });
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
    },
    onClickNotification: function(e) {
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
    showDatePicker: function(e) {
        $("#todo-date").datepicker({
            dateFormat: "yy-mm-dd"
        });
        $("#ui-datepicker-div").css("z-index", 1032);
    },
    removeTodo: function(e) {
        var self = this,
            clickedEl = $(e.target).parents(".todo-list-item")[0],
            modelIndex = $(".todo-list-item").index(clickedEl);

        this.model = this.collection.models[modelIndex];
        this.model.destroy({ success: function(model) {
            var record = self.collection.modelList[self.getTaskType(model.attributes.date_due)],
                modelIDs = _.pluck(record, 'id');

            record.splice(_.indexOf(modelIDs, model.id), 1);

            self.open = true;
            self._render();
        }});
    },
    changeStatus: function(e) {
        var clickedEl = $(e.target).parents(".todo-list-item")[0],
            modelIndex = $(".todo-list-item").index(clickedEl),
            taskStatusListStrings = app.lang.getAppListStrings('task_status_dom');

        // get the current model
        this.model = this.collection.models[modelIndex];
        var record = this.collection.modelList[this.getTaskType(this.model.attributes.date_due)],
            recordIndex = ( _.indexOf(_.pluck(record, 'id'), this.model.id) );

        if( this.model.attributes.status == taskStatusListStrings['Completed'] ) {
            this.model.set({
                "status": taskStatusListStrings['Not Started']
            });
            record[recordIndex].attributes.status = taskStatusListStrings['Not Started'];
        }
        else {
            this.model.set({
                "status": taskStatusListStrings['Completed']
            });
            record[recordIndex].attributes.status = taskStatusListStrings['Completed'];
        }

        this.model.save();
        this.open = true;
        this._render();
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
        var todayBegin = new Date(),
            todayEnd   = new Date(),
            todoStamp = app.date.parse(todoDate).getTime();

        todayBegin = todayBegin.setHours(0,0,0,0);
        todayEnd = todayEnd.setHours(23,59,59,999);

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

        if( subject == "" || !(app.date.parse(date, app.date.guessFormat(date))) ) {
            console.log("invalid input data");
            return false;
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
            this._render();
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
                self._render();
            }, this);
        }
    }
})