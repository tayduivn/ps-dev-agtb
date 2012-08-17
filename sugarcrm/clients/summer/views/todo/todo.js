({
    events: {
        'click #todo-container': 'onClickNotification',
        'click #todo': 'handleEscKey',
        'click #todo-add': 'todoSubmit',
        'keyup #todo-subject':'todoSubmit'
    },
    initialize: function(options) {
        var self = this;
        app.view.View.prototype.initialize.call(this, options);
        console.log("---------");
        console.log("initializing todo view");
        console.log(this);
        console.log(options);
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
    reset: function(context) {
        console.log();
    },
    _render: function() {
        console.log("---------");
        console.log("render");
        console.log(this);
        //console.log(app.additionalComponents.todo.collection);
        app.view.View.prototype._render.call(this);
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
            app.additionalComponents.todo.collection.add(this.model);
            this.model.save();
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