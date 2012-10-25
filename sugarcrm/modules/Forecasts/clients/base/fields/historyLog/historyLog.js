({
    /**
     * Do we show this field alert
     */
    showFieldAlert: false,

    /**
     * The User Id
     */
    uid: '',

    /**
     * Commit Date
     */
    commitDate: '',

    /**
     * Deferred object for manager worksheet render
     */
    mDeferred: $.Deferred(),

    /**
     * Deferred object for worksheet model being ready
     */
    wDeferred:$.Deferred(),

    bindDataChange: function() {
        var self = this;

        if(self.context && self.context.forecasts) {
            //Bind to the worksheetmanager render event so we know that the view has been rendered
            self.context.forecasts.on("forecasts:worksheetmanager:render", function() {
                self.mDeferred.resolve();
            });
            //Bind to the committed model being reset so we know that the model has been updated
            self.context.forecasts.committed.on("reset", function() {
                self.wDeferred.resolve();
            });
        }

        self.handleDeferredRender();
    },

    /**
     * Handles setting up the listeners for the two deferred objects.  When both conditions are satisfied
     * it calls _render and sets itself up again.
     *
     */
    handleDeferredRender: function() {
        var self = this;
        $.when(self.wDeferred, self.mDeferred).done(function() {
            self._render();
            //Reset the deferred objects
            self.wDeferred = self.mDeferred = $.Deferred();
            self.handleDeferredRender();
        });
    },

    /**
     * Overwrite the render method
     *
     * @return {*}
     * @private
     */

    _render:function () {
        if(this.context && !_.isEmpty(this.context.forecasts.committed.models)) {
            var commitDateStr = _.first(this.context.forecasts.committed.models).get('date_modified');
            var commitDate = new Date(commitDateStr);

            var fieldDateStr = this.model.get('date_modified');
            var fieldDate = new Date(fieldDateStr);

            // if fieldDate is newer than the forecast commitDate, then we want to show the field
            this.showFieldAlert = false;

            if (_.isDate(fieldDate) && _.isDate(commitDate)) {
                this.showFieldAlert = (fieldDate.getTime() > commitDate.getTime());
            }

            this.uid = this.model.get('user_id');
            this.commitDate = commitDate;

            this.options.viewName = 'historyLog';
            app.view.Field.prototype._render.call(this);
        }
        return this;
    }

})
