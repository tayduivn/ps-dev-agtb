({
    /**
     * Attach a click event to <a class="worksheetManagerLink"> field
     */
    events : { 'click a.worksheetManagerLink' : 'linkClicked' },

    /**
     * Holds the user_id for passing into userTemplate
     */
    uid: '',

    _render:function() {
        var self = this;
        if(this.name == 'name') {
            this.uid = this.model.get('user_id');
            this.popoverTitleName = this.model.get('name');

            // setting the viewName allows us to explicitly set the template to use
            this.options.viewName = 'userLink';
        }
        app.view.Field.prototype._render.call(this);
        return this;
    },

    /**
     * Handle a user link being clicked
     * @param event
     */
    linkClicked: function(event) {
        var uid = $(event.target).data('uid');
        var self = this;
        var selectedUser = {
            id: '',
            user_name:'',
            full_name: '',
            first_name: '',
            last_name: '',
            isManager: false,
            showOpps: this.model.get("show_opps")
        };

        var options = {
            dataType: 'json',
            context: selectedUser,
            success: function(data) {
                selectedUser.id = data.id;
                selectedUser.user_name = data.user_name;
                selectedUser.full_name = data.full_name;
                selectedUser.first_name = data.first_name;
                selectedUser.last_name = data.last_name;
                selectedUser.isManager = data.isManager;

                self.context.forecasts.set({selectedUser : selectedUser})
            }
        };

        myURL = app.api.buildURL('Forecasts', 'user/' + uid);
        app.api.call('read', myURL, null, options);
    },

    /**
     * Hides popover icon and removes event listener
     */
    hideIcon: function(){
        // hide icons
        $('.pull-right').hide();

        // remove event listener
        this.context.forecasts.off('change:commitForecastFlag', this.hideIcon);
    }
})