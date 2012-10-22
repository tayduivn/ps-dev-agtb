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
        if(this.context.forecasts.committed.models.length === 0) {
            return;
        }
        var commitDateStr = _.first(this.context.forecasts.committed.models).get('date_modified');
        var commitDate = app.forecasts.utils.parseDBDate(commitDateStr);

        var fieldDateStr = this.model.get('date_modified');
        var fieldDate = app.forecasts.utils.parseDBDate(fieldDateStr);

        // if fieldDate is newer than the forecast commitDate, then we want to show the field
        var showFieldAlert = false;
        if (_.isDate(fieldDate) && _.isDate(commitDate)) {
            this.showFieldAlert = (fieldDate.getTime() > commitDate.getTime());
        }

        this.uid = this.model.get('user_id');
        this.commitDate = commitDate;

        this.options.viewName = 'historyLog';
        app.view.Field.prototype._render.call(this);
        return this;
    }
})
