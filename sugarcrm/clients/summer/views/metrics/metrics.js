({

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);
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