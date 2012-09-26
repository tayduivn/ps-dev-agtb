({
    format:function(value){
        if (value && !value.match(/^(http|https):\/\//)) {
            value = "http://" + value;
        }
        return value
    }
})