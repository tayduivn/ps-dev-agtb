({

    /**
     * Used to determine whether or not to visibly show the Commit button
     */
    showCommitButton : true,

    /**
     * Used to determine whether or not the Commit button is enabled
     */
    commitButtonEnabled: false,

    /**
     * Adds event listener to elements
     */
    events: {
        "click a[id=commit_forecast]" : "triggerCommit",
        "click a[id=save_draft]" : "triggerSaveDraft"
    },

    /**
     * Fires during initialization and if any data changes on this model
     */
    bindDataChange: function() {
        var self = this;
        if(this.context && this.context.forecasts) {
            this.context.forecasts.on("change:selectedUser", function(context, user) {
                var oldShowButtons = self.showCommitButton;
                self.showCommitButton = self.checkShowCommitButton(user.id);
                // if show buttons has changed, need to re-render
                if(self.showCommitButtons != oldShowButtons) {
                    self._render();
                }
            });
            this.context.forecasts.on("change:commitButtonEnabled", this.commitButtonStateChangeHandler, self);
        }
    },

    /**
     * Renders the component
     * @private
     */
    _render: function() {
        app.view.View.prototype._render.call(this);

        if(this.showCommitButton) {
            if(this.commitButtonEnabled) {
                this.$el.find('a[id=commit_forecast]').removeClass('disabled');
                this.$el.find('a[id=save_draft]').removeClass('disabled');
            } else {
                this.$el.find('a[id=commit_forecast]').addClass('disabled');
                this.$el.find('a[id=save_draft]').addClass('disabled');
            }
        }
    },

    /**
     * Event Handler for when the context commitButtonEnabled variable changes
     * @param context
     * @param commitButtonEnabled boolean value for the changed commitButtonEnabled from the context
     */
    commitButtonStateChangeHandler: function(context, commitButtonEnabled){
        this.commitButtonEnabled = commitButtonEnabled;
        this._render();
    },

    /**
     * Sets the flag on the context so forecastsCommitted.js will call commitForecast
     */
    triggerCommit: function() {
        this.context.forecasts.set({commitForecastFlag: true});
    },

    /**
     * Handles Save Draft button being clicked
     */
    triggerSaveDraft: function() {
        //todo: implement save draft functionality, or trigger flag on context if save is handled elsewhere
    },

    /**
     * returns boolean value indicating whether or not to show the commit button
     */
    checkShowCommitButton: function(id) {
        return app.user.get('id') == id;
    }

})