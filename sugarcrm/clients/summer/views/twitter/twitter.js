
({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this,options);
    },

    injectTwitter: function( ) {
        console.log('inject twitter');
        var self = this;

        var script = "";
        script += "<script>";
        script += "new TWTR.Widget({";
        script +=    "version: 2,";
        script += "type: 'profile',";
        script += "rpp: 4,";
        script +=  "interval: 30000,";
        script +=    "width: 250,";
        script +=    "height: 300,";
        script +=     "theme: {";
        script +=     "shell: {";
        script +=     "background: '#333333',";
        script +=     "color: '#ffffff'";
        script +=   "},";
        script += "tweets: {";
        script +=    "background: '#000000',";
        script +=   "color: '#ffffff',";
        script +=     "links: '#4aed05'";
        script +=   "}";
        script += "},";
        script += "features: {";
        script +=    "scrollbar: false,";
        script +=    "loop: false,";
        script +=     "live: false,";
        script +=    "behavior: 'all'";
        script +=    "}";
        script += "}).render().setUser('sugarcrm').start();";
        script += "</script>";
        this.$(".twitter-profile").append(script);
        
    },

    bindDataChange: function () {
        var self = this;
        this.model.on('change', function () { self.injectTwitter(); }, this);
    }

})