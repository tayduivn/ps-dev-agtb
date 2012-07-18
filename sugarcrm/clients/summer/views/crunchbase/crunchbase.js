/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ListView
 * @alias SUGAR.App.layout.ListView
 * @extends View.View
 */
({
    events: {
    },


    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);
        var lid = this.options.lid || ""; // Layout Id
    },

    reset: function(context) {
        // If creating a new screen, lets hide the div.
        this.$el.toggle(!(context.id === "new"));
        this.model = context.data;
        this.model.bind("change", this.getData);
        this.getData();
    },

    _render: function() {
        if (this.name != 'crunchbase'){
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
        }
    },



    // If edit mode is turned on, set to show and then fade it
    toggle: function(editOn) {
        this.$el.toggle(editOn);
        this.$el.fadeToggle("fast");
    },


    getData: function() {
        var url;
        var name = this.model.get("name");
        if(!name)name = this.model.get('account_name');
        if(!name)name = this.model.get('full_name');
        var self = this;

        name = 'SugarCRM';

        if (name) {
            url = "http://api.crunchbase.com/v/1/company/" + name.toLowerCase().replace(/ /g, "-") + ".js?callback=?";
            $.ajax({
                url: url,
                dataType: "jsonp",
                success: function(data){
                    if(data.image) {
                        data['image'] = data.image.available_sizes[0][1];
                    }
                    self = _.extend(self, data);

                    app.view.View.prototype._renderHtml.call(self);
                },
                context: this
            });
        }
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
