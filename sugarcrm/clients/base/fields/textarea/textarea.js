({
    fieldTag : "textarea",
    format:function(value) {
        var newval = value.replace(/\n/g,'<BR>');
        return new Handlebars.SafeString(newval);
    }
})