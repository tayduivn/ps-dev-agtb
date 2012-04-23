({
    events: {
        'keyup .chzn-search input': 'throttleSearch'
    },
    fieldType: "select",
    initialize: function(options) {
        _.bindAll(this);
        console.log("initing relate");
        this.app.view.Field.prototype.initialize.call(this, options);
    },
    render: function() {
        console.log("rendering relate");
        var result = this.app.view.Field.prototype.render.call(this);
        $(this.fieldType + "[name=" + this.name + "]").chosen({no_results_text: "Searching for "});
        return result;
    },
    throttleSearch: function(e, interval) {
        console.log("throttling search")
        if (interval === 0) {
            this.search(e);
            return;
        } else {
            interval = 500;
            clearTimeout(this.throttling);
            delete this.throttling;
        }

        this.throttling = setTimeout(this.throttleSearch, interval, e, 0);
    },
    search: function(event) {
        console.log("calling search callback", event);
        console.log(event.target.value);
        console.log("this", this);
        var self = this;
        var collection = app.data.createBeanCollection(this.module);
        collection.fetch({
       params: [
            {key: "q", value: "bob"}
        ],
       success: function(data) {
            console.log("we got this data", data, data.models.length);
           if (data.models.length >0) {
               self.options = data.models;
               var test = '<option value="Max Jensen" selected="">asdf</option><option value="Max Jensen" selected="">noino</option>';


               console.log("self", self);
               self.getOptionsTemplate();
               var options = self.optionsTemplateC(self);
               console.log("new options", options);
               self.$('select').append(options);
               self.$('select').trigger("liszt:updated");
           }  else {
               //TODO trigger we found nothing
           }
        }

    });
},
    getOptionsTemplate: function(){
        var templateKey = "sugarField." + this.type + ".options";

        var templateSource = null;

        if (this.meta) {
            templateSource = this.meta.views["options"];
        }
        this.optionsTemplateC = app.template.get(templateKey) || app.template.compile(templateSource, templateKey);
    },
    bindDomChange: function(model, fieldName) {
        var self = this;
        var el = this.$el.find(this.fieldType);
        // Bind input to the model
        el.on("change", function(ev) {
            model.set(fieldName, self.unformat(el.val()));
        });
        this.$('select').chosen().change(function(event){
            console.log("value", event.target.val())
            //model.set(fieldName,)
        });
    }
})