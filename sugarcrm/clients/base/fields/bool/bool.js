({
    unformat:function(value){
        value = this.$el.find('input[type=checkbox]')[0].checked ? "1" : "0";
        return value
    },
    format:function(value){
        value = (value=="1") ? true : false;
        return value
    }
})