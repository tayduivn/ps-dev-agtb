({
    /**
     * Attach a Change event to the field
     */
    events : { 'change' : 'bucketsChanged' },
    
    /**
     * Render Field
     */
    _render:function () {
        var forecastCategories = this.context.forecasts.config.get("forecast_ranges");
        this.disabled = false;
       
        //Check to see if you're a manager on someone else's sheet, disable changes
        if(this.context.forecasts.get("selectedUser")["id"] != app.user.id){
            this.disabled = true;
        }
        
        //show_binary, show_buckets, show_n_buckets logic
        if(forecastCategories == "show_binary"){
            //If we're in binary mode
            this.def.view = "bool";
            this.format = function(value){
                return value == "include";
            };
            this.unformat = function(value){
                return this.$el.find(".checkbox").prop('checked') ? "include" : "exclude";
            };
        }
        else if(forecastCategories == "show_buckets"){
            //Show buckets, but only if we are on our sheet.
            if(!this.disabled){
                this.def.view = "enum";
                this.createBuckets();
            }
            else{
                this.def.view = "default";
                this.getLanguageValue();
            }
        }
        
        app.view.Field.prototype._render.call(this);
        
        //If we are on our own sheet, and need to show the dropdown, init things
        if(!this.disabled && this.def.view == "enum"){
            this.$el.find("option[value=" + this.value + "]").attr("selected", "selected");
            this.$el.find("select").chosen();
        }
    },
    
    /**
     * Change handler for the buckets field
     */
    bucketsChanged: function(){
        var self = this;
        var values = {};
        var moduleName = self.moduleName;
        
        if(self.def.view == "bool"){
            self.value = self.unformat();
            values[self.name] = self.value;
        }
                    
        values["timeperiod_id"] = self.context.forecasts.get("selectedTimePeriod").id;
        values["current_user"] = app.user.get('id');
        values["isDirty"] = true;
        
        //If there is an id, add it to the URL
        if(self.model.isNew())
        {
            self.model.url = app.api.buildURL(moduleName, 'create');
        } else {
            self.model.url = app.api.buildURL(moduleName, 'update', {"id":self.model.get('id')});
        }
        
        self.model.set(values);
    },
    
    /**
     * Creates the HTML for the bucket selectors
     * 
     * This function is used to create the select tag for the buckets.  For performance reasons, we only want
     * to iterate over the option list once, so we do that here and store it as a jQuery data element on the Body tag.
     * Also, we check to make sure this hasn't already been done (so we don't do it again, of course).
     */
    createBuckets: function(){
        var self = this;
        self.buckets = $.data(document.body, "buckets");
        
        if(_.isUndefined(self.buckets)){
            var options = app.lang.getAppListStrings(this.def.options) || 'commit_stage_dom';
            self.buckets =  "<select data-placeholder=' ' name='" + self.name + "' style='width: 100px;'>";
            self.buckets +=     "<option value='' selected></option>";
            _.each(options, function(item, key){
                self.buckets += "<option value='" + key + "'>" + item + "</options>"
            });
            self.buckets += "</select>";
            $.data(document.body, "buckets", self.buckets);
        }
    },
    
    /**
     * Gets proper value of the item out of the language file.
     * 
     * If we are in buckets mode and are on a non-editable sheet, we need to display the proper value of this
     * field as determined by the language file.  This function sets the proper key in the field for the hbt to pick it up and
     * display it.
     */
    getLanguageValue: function(){
        var options = app.lang.getAppListStrings(this.def.options) || 'commit_stage_dom';
        this.langValue = options[this.model.get(this.def.name)];
    }
})
