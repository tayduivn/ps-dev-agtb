({

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);
    },

    reset: function(context) {
        console.log("resetting");
        this.$el.hide();
        this.getData();
        this._render();
    },

    _render: function(o) {
        console.log("Metrics Render");
        this.$el.show();
        app.view.View.prototype._render.call(this);
    },

    getData: function() {

        var url = 'rest/Reports/data/boxStats';
        $.ajax({
            url: url,
            dataType: "json",
            success: function(data){
                self = _.extend(self, data);
                app.view.View.prototype._renderHtml.call(self);
            },
            context: this
        });
    },

    bindDataChange: function() {
        var self = this;
        if (this.model) {
            this.model.on("change", function() {
                self.getData();
            }, this);
        }
    }
})