({
    unformat:function(value){
        value = (value!='' && value!='http://') ? value : "";
        return value;
    }
})