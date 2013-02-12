({
    /**
     * Remove id and status fields when duplicating a Task
     * @param prefill
     */
    setupDuplicateFields: function(prefill){
        var duplicateBlackList = ["id", "status"];
        _.each(duplicateBlackList, function(field){
            if(field && prefill.has(field)){
                prefill.unset(field);
            }
        });
    }
})
