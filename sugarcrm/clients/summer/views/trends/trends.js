({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
    },

    getData: function() {
        var name = this.model.get("name") || this.model.get('account_name') || this.model.get('full_name'),
            script;

        if (name) {
            script = "";
            script += "<iframe src=";
            script += "http://www.gmodules.com/ig/ifr?url=http://www.google.com/ig/modules/trends_gadget.xml&amp;source=imag&amp;up_is_init=true&amp;up_cur_term=";
            script += name;
            script += "&amp;up_date=mtd&amp;up_region=US";
            script += " style=";
            script += " border:1px solid #ccc; padding:10px;"
            script += " width=";
            script += " 500";
            script += " height=";
            script += " 250";
            script += " frameborder=";
            script += " 0";
            script += " scrolling=";
            script += " no";
            script += "></iframe>";
            this.$(".trendsprofile").html(script);
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