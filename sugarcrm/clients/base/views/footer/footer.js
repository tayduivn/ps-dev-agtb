({
    events: {
        'click #tour': 'systemTour',
        'click #print': 'print',
        'click #top': 'top',
        'click #languageList .dropdown-menu a' : 'setLanguage',
        'click #instance': 'instanceMenu',
        'click #invite': 'invite',
        'click #instancesContainer': 'ignore',
    },
    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
        app.view.View.prototype.initialize.call(this, options);

        // Format the list of languages for the template
        var languages = app.lang.getAppListStrings('available_language_dom');
        this.languageList = [];
        for (var languageKey in languages) {
            if (languageKey !== "")
            this.languageList.push({
                key: languageKey,
                value: languages[languageKey]
            })
        }
    },
    _renderHtml: function() {
        this.isAuthenticated = app.api.isAuthenticated();
        this.currentLang = app.lang.getLanguage() || "en_us";

        if (app.config && app.config.logoURL) {
            this.logoURL=app.config.logoURL;
        }
        this.instance_name = app.user.get('instance_name');
        app.view.View.prototype._renderHtml.call(this);
    },
    systemTour: function() {
        this.$('#systemTour').modal('show');
    },
    print: function() {
        window.print();
    },
    top: function() {
        scroll(0,0);
    },
    setLanguage: function(e) {
        app.lang.hasChanged = true;
        var $li = this.$(e.currentTarget),
            langKey = $li.data("lang-key");
        app.alert.show('language', {level: 'warning', title: 'LBL_LOADING_LANGUAGE', autoclose: false});
        app.lang.setLanguage(langKey, function() { app.alert.dismiss('language'); });
    },
    instanceMenu: function(e) {
    	App.api.call('GET', '../rest/v10/summer/office', null, {
    		success: function(o) {
    			//console.log(o);
    			$("#instanceList").html("");
    			$("#instanceList")
    			for(i=0; i<o.instances.length; i++) {
    				$("#instanceList").append("<li>"+o.instances[i].name+"</li>");
    			}
    			$("#usersList").html("");
    			$("#usersList")
    			for(i=0; i<o.users.length; i++) {
    				$("#usersList").append("<li>"+o.users[i].first_name+" "+o.users[i].last_name+", last login: " + o.users[i].login_time+"</li>");
    			}
    		}
    	});
    },
    invite: function(e) {
    	email = $("#inviteemail").val();
    	if(!email) {
    		return;
    	}
    	var self = this;
    	App.api.call('create', '../rest/v10/summer/invite', {email: email}, {
    		success: function(o) {
    			$("#inviteemail").val('');
    			//self.showMessage('Invited', 3000);
    		}
    	});
    },
    ignore: function(e) {
    	e.stopPropagation();
    },
})
