({
    events: {
        'click #todo-container': 'onClickNotification',
        'click #todo': 'handleEscKey',
        'click #todo-add': 'todoSubmit',
        'keyup #todo-subject':'todoSubmit',
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
            self.collection.fetch();
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
    changeStatus: function(e) {
        console.log("---------");
        console.log("changeStatus");
        //console.log(this);
        //console.log(e);
        var clickedEl = $(e.target).parents(".todo-list-item")[0];
        var modelIndex = $(".todo-list-item").index(clickedEl);

        console.log(this.collection.models[modelIndex]);
        console.log(app.additionalComponents.todo.collection.models[modelIndex]);
        if( this.collection.models[modelIndex].attributes.status == "Completed" ) {
            // todo: localize this
            app.additionalComponents.todo.collection.models[modelIndex].set({
                "status": "In Progress"
            });
        }
        else {
            // todo: localize this
            app.additionalComponents.todo.collection.models[modelIndex].set({
                "status": "Completed"
            });
        }

        app.additionalComponents.todo.collection.models[modelIndex].save();
        console.log(app.additionalComponents.todo.collection.models[modelIndex]);
        this._render();
        //console.log(this.collection.where({"name": "mang"}));
        //console.log(this.collection.models[0]);
        // figure out which model was clicked, set it = this.model
        // toggle status to completed/not started, save the model
        // call _render(), since hbt file will change styling
    },
    _render: function() {
        console.log("---------");
        console.log("render");
        console.log(this);
        //console.log(app.additionalComponents.todo.collection);

        // try e.stopPropagation() and e.preventDefault() to prevent
        // closing the dropup menu maybe

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
            this.model = app.data.createBean("Tasks", {"name": subject});
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