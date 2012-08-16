({
    events: {
        'click #todo-container': 'onClickNotification',
        'click #todo': 'handleEscKey',
        'click #todo-add': 'todoSubmit',
        'keyup #todo-subject':'todoSubmit'
    },
    initialize: function(options) {
        var self = this;
        app.events.on("app:sync:complete", function() {
            self.collection = app.data.createBeanCollection("Tasks");
            self.collection.fetch();
        });
        app.view.View.prototype.initialize.call(this, options);
        console.log("initializing todo view");
        console.log(this);
        console.log(options);
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
    renderTodo: function(model) {
        console.log("render");
        console.log(this);
        console.log(model);
        console.log("---");
        console.log(app.additionalComponents.todo.collection);
        $("#todo-subject").val("");
    },
    validateTodo: function(e) {
        var subject = $("#todo-subject").val();
        if( subject == "" ) {
            console.log("invalid input data");
            return false;
        }
        else {
            this.model = app.data.createBean("Tasks", {"name": subject});
            this.model.save();
            app.additionalComponents.todo.collection.add(this.model);

            this.renderTodo(this.model);
            this.render();
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
    }
})