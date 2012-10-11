({
    unformat: function(value) {
        return this.app.currency.unformatAmountLocale(value);
    },
    format: function(value) {
        var currencyId = this.model.attributes.currency_id || '-99';
        // do we convert to base currency?
        if(this.def.convertToBase) {
            value = this.app.currency.convertToBase(value, currencyId);
        }
        // if necessary, unformat first
        if(/[^\d]/.test(value))
        {
            value = this.unformat(value);
        }
        return app.currency.formatAmountLocale(value, currencyId);
    }
})