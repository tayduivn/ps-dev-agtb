({
    unformat: function(value) {
        value = app.utils.unformatNumberString(value, this.number_group_seperator, this.decimal_seperator, false);
        return value
    },
    format: function(value) {
        value = app.utils.formatNumber(value, this.round, this.precision, this.number_group_seperator, this.decimal_seperator);
        return value
    }
})