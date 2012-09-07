({
    unformat: function(value) {
        return this.app.currency.unformatAmountLocale(value);
    },
    format: function(value) {
        var currency_id, base_rate;
        // do we convert to base currency?
        if(this.convertToBase) {
            // base currency id is -99
            currency_id = '-99';
            // get rate to convert this amount to base
            base_rate = this.model.attributes.base_rate || 1.0;
            value = value * base_rate;
        } else {
            // use transaction currency, use base if not found
            currency_id = this.model.attributes.currency_id || '-99';
        }
        // if necessary, unformat first
        if(/[^\d]/.test(value))
        {
            value = this.unformat(value);
        }
        return app.currency.formatAmountLocale(value, currency_id);
    }
})