({
    unformat:function(value){
        value = this.$el.find(".checkbox").prop("checked") ? "1" : "0";
        return value
    },
    format:function(value){
        value = (value=="1") ? true : false;
        return value
    }
})