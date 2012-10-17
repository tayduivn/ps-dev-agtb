({
    unformat: function(value) {
        return app.currency.unformatAmountLocale(value);
    },
    format: function(value) {
        var base_rate = this.model.attributes.base_rate;
        var currencyId = this.model.attributes.currency_id;
        // do we convert to base currency?
        if(this.def.convertToBase) {
            value = app.currency.convertWithRate(value, base_rate);
        }
        // if necessary, unformat first
        if(/[^\d]/.test(value))
        {
            value = this.unformat(value);
        }
        return app.currency.formatAmountLocale(value, currencyId);
    }
})