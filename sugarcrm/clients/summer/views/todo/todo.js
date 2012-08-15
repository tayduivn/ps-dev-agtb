({
    events: {
        'click #todo-container': 'onClickNotification',
        'click #todo': 'handleEscKey',
        'click #todo-add': 'todoSubmit',
        'keyup #todo-description':'todoSubmit'
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
        console.log(options.context.id);
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
    renderTodo: function(e) {
        console.log("render");
        console.log(e);
    },
    addOne: function(model) {
        //todoList.add(model);
        //console.log(todoList.toJSON());
        //$(".todo-list").append('<li style="list-style: none"><label style="display: inline" class="checkbox"><input type="checkbox"></label>' + _.escape(model.get("description")) + '</li>');
        console.log(this.el);
    },
    validateTodo: function(e) {
        var desc = $("#todo-description").val();
        if( desc == "" ) {
            console.log("invalid input data");
            return false;
        }
        else {
            //var todoItem = new TodoItem({"description": desc});
            console.log(this);
            //this.addOne(todoItem);
            $("#todo-description").val("");
        }
    },
    todoSubmit: function(e) {
        if( e.target.id == "todo-description" ) {
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