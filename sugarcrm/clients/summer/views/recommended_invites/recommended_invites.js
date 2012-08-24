({
    events: {
    },
    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
        App.api.call('GET', '../rest/v10/summer/recommend', null, {
        	success: function(o) {
        		$("#invitesList").html("");
        		for(i=0;i<o.invites.length;i++) {
        			$("#invitesList").append("<li>"+o.invites[i].first_name+" "+o.invites[i].last_name+" <strong>&lt;"+o.invites[i].email+"&gt;</strong></li>");
        		}
        	}
        });
    }
})
