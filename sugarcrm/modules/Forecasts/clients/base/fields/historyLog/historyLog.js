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
