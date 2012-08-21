({
    events: {
        'click #todo-container': 'onClickNotification',
        'click #todo': 'handleEscKey',
        'click #todo-add': 'todoSubmit',
        'keyup #todo-subject':'todoSubmit',
        'focus #todo-date': 'showDatePicker',
        'click .todo-status': 'changeStatus'
    },
    initialize: function(options) {
        var self = this;
        console.log("---------");
        console.log("initializing todo view");
        console.log(this);
        console.log(options);
        app.view.View.prototype.initialize.call(this, options);
        app.events.on("app:sync:complete", function() {
            console.log("---------");
            console.log("app:sync:complete");

            self.collection = app.data.createBeanCollection("Tasks");

            // If admin, grab all the todos
            if( app.user.get("id") == 1 ) {
                self.collection.fetch();
            }
            else
            {
                self.collection.fetch({myItems: true});
            }

            console.log(self);
            console.log(self.collection);
            self.bindDataChange();
        });
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
                    console.log("escaped");
                    $("#todo-container").parent().attr("class", "btn-group dropup");
                }
            }
        });
    },
    showDatePicker: function() {
        console.log("---------");
        console.log("showDatePicker");
    },
    changeStatus: function(e) {
        console.log("---------");
        console.log("changeStatus");

        var clickedEl = $(e.target).parents(".todo-list-item")[0];
        var modelIndex = $(".todo-list-item").index(clickedEl);

        // get the current model
        this.model = this.collection.models[modelIndex];

        //console.log(app.additionalComponents.todo.collection.models[modelIndex]);

        if( this.model.attributes.status == "Completed" ) {
            this.model.set({
                "status": app.metadata.data.appListStrings.task_status_default
            });
        }
        else {
            this.model.set({
                "status": app.metadata.data.appListStrings.task_status_dom["Completed"]
            });
        }

        this.model.save();
        this._render();
    },
    _renderHtml: function() {
        if (!app.api.isAuthenticated()) return;
        if (app.config && app.config.logoURL) {
            this.logoURL=app.config.logoURL;
        }
        app.view.View.prototype._renderHtml.call(this);
    },
    _render: function() {
        console.log("---------");
        console.log("render");
        console.log(this);

        app.view.View.prototype._render.call(this);
    },
    validateTodo: function(e) {
        var subject = $("#todo-subject").val();
        if( subject == "" ) {
            // change the input field styling to error class
            console.log("invalid input data");
            return false;
        }
        else {
            this.model = app.data.createBean("Tasks", {
                "name": subject,
                "assigned_user_id": "seed_" + app.user.get("user_name") + "_id"
            });
            app.additionalComponents.todo.collection.add(this.model);
            this.model.save();
            $("#todo-subject").val("");
            this._render();
        }
    },
    todoSubmit: function(e) {
        if( e.target.id == "todo-subject" ) {
            // if enter was pressed
            if( e.keyCode == 13 ) {
                // validate
                this.validateTodo(e);
            }
        }
        else {
            // validate
            this.validateTodo(e);
        }
    },
    bindDataChange: function() {
        var self = this;
        console.log("---------");
        console.log("inside bindDataChange");
        if (this.collection) {
            this.collection.on("reset", function() {
                console.log(self.collection);
                self._render();
            }, this);
        }
    }
})