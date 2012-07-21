({

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);
        var lid = this.options.lid || ""; // Layout Id

    },

    reset: function(context) {
        this.$el.toggle(!(context.id === "new"));
        this.model = context.data;
        this.model.bind("change", this.getData);
        this.getData();
    },

    _render: function(o) {

            this.$el.show();
            app.view.View.prototype._render.call(this);
            this.$("a.googledoc-fancybox").fancybox({
                'width': '95%',
                'height': '95%',
                'autoScale': true,
                'transitionIn': 'fadeIn',
                'transitionOut': 'fadeOut',
                'type': 'iframe'
            });

    },

    getData: function() {
        var url;
        var name = this.model.get("name");
        if(!name)name = this.model.get('account_name');
        if(!name)name = this.model.get('full_name');
        var self = this;
        if (name) {
            url = "https://ajax.googleapis.com/ajax/services/search/news?v=1.0&q=" + name.toLowerCase();
            $.ajax({
                url: url,
                dataType: "jsonp",
                success: function(data){
                    self = _.extend(self, data);
                    app.view.View.prototype._renderHtml.call(self);
                },
                context: this
            });
        }
    },

    // If edit mode is turned on, set to show and then fade it
    toggle: function(editOn) {
        console.log("Toggle " + editOn);
        this.$el.toggle(editOn);
        this.$el.fadeToggle("fast");
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