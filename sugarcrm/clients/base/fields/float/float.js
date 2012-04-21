({
    unformat: function(value) {
        value = this.app.utils.unformatNumberString(value, this.fieldDef.number_group_seperator, this.fieldDef.decimal_seperator, false);
        return value
    },
    format: function(value) {
        value = this.app.utils.formatNumber(value, this.fieldDef.round, this.fieldDef.precision, this.fieldDef.number_group_seperator, this.fieldDef.decimal_seperator);
        return value
    }
})