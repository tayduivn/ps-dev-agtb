({
    unformat: function(value) {
        value = this.app.utils.unformatNumberString(value, this.app.user.get('number_grouping_separator'), this.app.user.get('decimal_separator'), false);
        return value;
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
        return app.currency.formatAmountLocale(value, currency_id);
    }
})