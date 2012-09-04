({
    unformat: function(value) {
        value = this.app.utils.unformatNumberString(value, this.app.user.get('number_grouping_separator'), this.app.user.get('decimal_separator'), false);
        return value
    },
    format: function(value) {
        var currency_id, base_rate, currency_info;
        // do we convert to base currency?
        if(this.convertToBase) {
            // base currency id is -99
            currency_id = '-99';
            // get rate to convert this amount to base
            base_rate = this.model.attributes.base_rate;
            // convert to base rate
            if(!isNaN(base_rate)) {
                value = value * base_rate;
            }
        } else {
            // use transaction currency
            currency_id = this.model.attributes.currency_id;
        }
        if(currency_id && app.metadata.data.currencies[currency_id]) {
            currency_info = app.metadata.data.currencies[currency_id];
        }
        value = this.app.utils.formatNumber(value, this.def.round, this.app.user.get('decimal_precision'), this.app.user.get('number_grouping_separator'), this.app.user.get('decimal_separator'));
        if(currency_info && currency_info.symbol) {
            return currency_info.symbol + value;
        }
        return value;
    }
})