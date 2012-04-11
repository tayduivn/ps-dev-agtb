({
    unformat: function(value) {
        value = SUGAR.App.utils.unformatNumberString(value, this.number_group_seperator, this.decimal_seperator, false);
        return value
    },
    format: function(value) {
        value = SUGAR.App.utils.formatNumber(value, this.round, this.precision, this.number_group_seperator, this.decimal_seperator);
        return value
    }
})