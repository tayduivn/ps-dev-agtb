({
    /**
     * Do we show this field alert
     */
    showFieldAlert: false,

    /**
     * The User Id
     */
    uid: '',

    deferredObj: $.Deferred(),

    /**
     * Commit Date
     */
    commitDate: '',

    isRenderedMgrWksht: false,

    isRenderedCommitted: false,

    bindDataChange: function() {
        var self = this;
        if(self.context && self.context.forecasts) {


            self.context.forecasts.on("forecasts:worksheetmanager:render", function() {
                console.log('a');
                self.isRenderedMgrWksht = true;
                if(self.isRenderedCommitted) {
                   console.log('a called');
                   self.deferredObj.resolve();
                }
            });

            self.context.forecasts.on("forecasts:committed:updatedTotals", function() {
                console.log('b');
                self.isRenderedCommitted = true;
                if(self.isRenderedMgrWksht) {
                   console.log('b called');
                   self.deferredObj.resolve();
                }
            });
        }

        $.when(self.deferredObj).then(function() {
            console.log('fooo');
            self.isRenderedMgrWksht = self.isRenderedCommitted = false;
            self.deferredObj = $.Deferred();
            self._render();
        });
    },

    /**
     * Overwrite the render method
     *
     * @return {*}
     * @private
     */
    _render:function () {
        if(!_.isEmpty(this.context.forecasts.committed.models)) {
            var commitDateStr = _.first(this.context.forecasts.committed.models).get('date_modified');
            var commitDate = new Date(commitDateStr);

            var fieldDateStr = this.model.get('date_modified');
            var fieldDate = new Date(fieldDateStr);

            // if fieldDate is newer than the forecast commitDate, then we want to show the field
            var showFieldAlert = false;
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
