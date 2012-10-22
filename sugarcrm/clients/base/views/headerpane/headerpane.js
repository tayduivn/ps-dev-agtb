({
    events: {
        'click .record-edit': 'editClicked',
        'click .record-save': 'saveClicked',
        'click .record-cancel': 'cancelClicked',
        'click .record-delete': 'deleteClicked'
    },

    // button states
    STATE: {
        EDIT: 'edit',
        VIEW: 'view'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.context.on('headerpane:title:render', this.renderTitle, this);
        this.initButtonStatesListeners();
    },

    /**
     * Listen to context events to change button states
     */
    initButtonStatesListeners: function() {
        this.context.on('headerpane:buttons:edit', function() {
            this.setButtonStates(this.STATE.EDIT);
        }, this);
        this.context.on('headerpane:buttons:view', function() {
            this.setButtonStates(this.STATE.VIEW);
        }, this);
    },

    editClicked: function(event) {
        if (!this.$(event.target).hasClass('disabled')) {
            this.context.trigger('headerpane:edit:click');
        }
    },

    saveClicked: function(event) {
        if (!this.$(event.target).hasClass('disabled')) {
            this.setButtonStates(this.STATE.VIEW);
            this.context.trigger('headerpane:save:click');
        }
    },

    cancelClicked: function(event) {
        if (!this.$(event.target).hasClass('disabled')) {
            this.setButtonStates(this.STATE.VIEW);
            this.context.trigger('headerpane:cancel:click');
        }
    },

    deleteClicked: function(event) {
        if (!this.$(event.target).hasClass('disabled')) {
            this.context.trigger('headerpane:delete:click');
        }
    },

    _renderHtml: function() {
        this.checkAclForButtons();
        app.view.View.prototype._renderHtml.call(this);
    },

    /**
     * Check to see if the buttons should be displayed
     */
    checkAclForButtons: function() {
        if (this.context.get("model").module == "Users") {
            this.hasAccess = (app.user.get("id") == this.context.get("model").id);
        } else {
            this.hasAccess = app.acl.hasAccessToModel("edit", this.model);
        }
    },

    /**
     * Display title in the headerpane
     * @param title
     */
    renderTitle: function(title) {
        this.$('.headerpane-title').text(title);
    },

    /**
     * Change the behavior of buttons depending on the state that they should be in
     * @param state
     */
    setButtonStates: function(state) {
        if ( !this.$buttons ) {
            this.$buttons = {
                edit:   this.$('.record-edit'),
                save:   this.$('.record-save'),
                cancel: this.$('.record-cancel'),
                del:    this.$('.record-delete')
            };
        }

        var $buttons = this.$buttons;

        switch (state) {
            case this.STATE.EDIT:
                $buttons.edit.toggleClass('hide', false).addClass('disabled');
                $buttons.save.toggleClass('hide', false);
                $buttons.cancel.toggleClass('hide', false);
                $buttons.del.toggleClass('hide', false);
                break;
            case this.STATE.VIEW:
                $buttons.edit.toggleClass('hide', false).removeClass('disabled');
                $buttons.save.toggleClass('hide', true);
                $buttons.cancel.toggleClass('hide', true);
                $buttons.del.toggleClass('hide', false);
                break;
            default:
                $buttons.edit.toggleClass('hide', true).removeClass('disabled');
                $buttons.save.toggleClass('hide', true);
                $buttons.cancel.toggleClass('hide', true);
                $buttons.del.toggleClass('hide', true);
                break;
        }
    }
})