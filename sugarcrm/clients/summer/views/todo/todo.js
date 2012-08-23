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

            // If admin, grab all the todos
            if( app.user.get("id") == 1 ) {
                self.collection.fetch();
            }
            else
            {   // otherwise, grab user-specific todos
                self.collection.fetch({myItems: true});
            }

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
        var self = this;
        var clickedEl = $(e.target).parents(".todo-list-item")[0];
        var modelIndex = $(".todo-list-item").index(clickedEl);

        this.model = this.collection.models[modelIndex];
        this.model.destroy({success: function() {
            self._render();
        }});
    },
    changeStatus: function(e) {
        var clickedEl = $(e.target).parents(".todo-list-item")[0];
        var modelIndex = $(".todo-list-item").index(clickedEl);

        // get the current model
        this.model = this.collection.models[modelIndex];

        if( this.model.attributes.status == app.lang.getAppListStrings('task_status_dom')['Completed'] ) {
            this.model.set({
                "status": app.lang.getAppListStrings('task_status_dom')['Not Started']
            });
        }
        else {
            this.model.set({
                "status": app.lang.getAppListStrings('task_status_dom')['Completed']
            });
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
    validateTodo: function(e) {
        var subject = $("#todo-subject").val();
        var date = $("#todo-date").val();
        if( subject == "" || !(app.date.parse(date, app.date.guessFormat(date))) ) {
            console.log("invalid input data");
            return false;
        }
        else {
            this.model = app.data.createBean("Tasks", {
                "name": subject,
                "assigned_user_id": "seed_" + app.user.get("user_name") + "_id",
                "date_due": date + " 00:00:00"
            });
            this.collection.add(this.model);
            this.model.save();
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