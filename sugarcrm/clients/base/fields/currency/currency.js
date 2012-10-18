({
    transactionValue: '',
    /**
     * setup transactional amount if flag is present and transaction currency is not base
     *
     * @return {Object} this
     * @private
     */
    _render: function() {
        if(this.def.convertToBase && this.def.showTransactionalAmount && this.model.get('currency_id') !== '-99') {
            this.transactionValue = app.currency.formatAmountLocale(
              this.model.get(this.name),
              this.model.get('currency_id')
            );
        }
        app.view.Field.prototype._render.call(this);
        return this;
    },
    /**
     * unformat the field
     *
     * @param {String} value the displayed string
     * @return {String} value
     */
    unformat: function(value) {
        return app.currency.unformatAmountLocale(value);
    },
    /**
     * format the field, convert to base if necessary
     *
     * @param {String} value the displayed string
     * @return {String} value
     */
    format: function(value) {
        var base_rate = this.model.get('base_rate');
        var currencyId = this.model.get('currency_id');
        // do we convert to base currency?
        if(this.def.convertToBase) {
            value = app.currency.convertWithRate(value, base_rate);
            currencyId = '-99';
        }
        // if necessary, unformat first
        if(/[^\d]/.test(value))
        {
            value = this.unformat(value);
        }
        return app.currency.formatAmountLocale(value, currencyId);
    }
})