({
    getData: function() {
        var url = 'rest/Reports/data/boxStats';

        $.ajax({
            url: url,
            dataType: "json",
            success: function(data){
                _.extend(this, data);
                this.render();
            },
            context: this
        });
    },

    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", this.getData, this);
        }
    }
})