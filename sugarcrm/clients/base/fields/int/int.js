({
    unformat:function(value){
        value = SUGAR.App.utils.formatNumber(value, 0, 0, "", ".");
        return value
        },
    format:function(value){
        value = SUGAR.App.utils.formatNumber(value, 0, 0, this.number_group_seperator, ".");
        return value
    }
})