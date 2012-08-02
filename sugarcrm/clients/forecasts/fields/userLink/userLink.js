({
    /**
     * Attach a click event to <a class="worksheetManagerLink"> field
     */
    events : { 'click a.worksheetManagerLink' : 'linkClicked' },

    /**
     * Template to use when displaying clickable name links in the manager worksheet
     * data-uid carries the user_id through the event
     */
    userTemplate : _.template('<a href="javascript:void(0)" class="worksheetManagerLink" data-uid="<%= uid %>"><%= value %></a>'),

    /**
     * Template to use when displaying simple values
     */
    valueTemplate :_.template('<%= value %>'),

    /**
     * Holds the user_id for passing into userTemplate
     */
    uid: '',

    _render:function() {
        var tpl = this.valueTemplate;

        if(this.name == 'name') {
            tpl = this.userTemplate;
            this.uid = this.model.get('user_id');
        }

        this.template = tpl;

        if (this.model instanceof Backbone.Model) {
            /**
             * Model property value.
             * @property {String}
             * @member View.Field
             */
            this.value = this.format(this.model.has(this.name) ? this.model.get(this.name) : "");
        }

        this.$el.html(this.template(this));
        this.unbindDom();
        this.bindDomChange();
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
                selectedUser.full_name = data.full_name;
                selectedUser.first_name = data.first_name;
                selectedUser.last_name = data.last_name;
                selectedUser.isManager = data.isManager;

                self.context.forecasts.set({selectedUser : selectedUser})
            }
        };

        myURL = app.api.buildURL('Forecasts', 'user/' + uid);
        app.api.call('read', myURL, null, options);
    }
})
