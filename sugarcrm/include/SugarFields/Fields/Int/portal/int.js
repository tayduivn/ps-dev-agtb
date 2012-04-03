{
    unformat:function(value){
        value = SUGAR.App.utils.formatNumber(value, 1, 0, "", ".");
        return value
        },
    format:function(value){
        value = SUGAR.App.utils.formatNumber(value, 1, 0, this.number_group_seperator, ".");
        return value
    }
}