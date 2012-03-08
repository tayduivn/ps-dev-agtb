(function (app) {

    var sfid = 0;
    //Register Handlebars helper
    Handlebars.registerHelper('sugar_field', function (context, view, bean) {
        var ret = '<span sfuuid="' + (++sfid) + '"></span>';
        var name = this.name;
        bean = bean || context.get("model");
        var sf = view.sugarFields[sfid] || (view.sugarFields[sfid] = app.sugarFieldManager.get({
            def: bean.fields[name] || this,
            view : view,
            context : context,
            model : bean || context.get("model")
        }));

        sf.sfid = sfid;
        return new Handlebars.SafeString(ret);
    });

    app.augment('SugarField', Backbone.View.extend({
        app : app,
        template : null,
        sfid : -1,

        initialize: function(options) {
            var templateKey;
            _.extend(this, options.def);
            this.view = options.view;
            this.label = this.label || this.name;
            this.bind(options.context, options.model || options.context.get("model"));
            this.viewName = this.view.name;
            this.meta = app.metadata.get({sugarField:this});
            templateKey = "sugarField." + this.name + "." + this.view.name;
            this.templateC = app.template.get(templateKey);
            if (!this.templateC)
                this.templateC = app.template.compile(this.meta.template, templateKey);
        },

        //TODO: Convert string function names to references to the callback function
        //Then call the parent delegate
        delegateEvents : function(events){
            if (!(events || (events = this.events))) return;
            events = _.clone(events);
            for (var key in events) {
                var method = events[key];
                if (!_.isFunction(method)) method = this[events[key]];
                if (!method){
                    if (_.isString(events[key])){
                        try{
                            method = eval("(" + events[key] + ")");
                        } catch(e) {
                            app.logger.error("invalid event callback " + key + " : " + events[key]);
                            delete events[key];
                        }
                    }
                    if (_.isFunction(method)) {
                        this["callback_" + key] = method;
                        events[key] = "callback_" + key;
                    }
                }
            }
            Backbone.View.prototype.delegateEvents.call(this, events);
        },

        render : function(){
            this.value = this.model.has(this.name) ? this.model.get(this.name) : "";
            this.$el.html(this.templateC(this));

            var model = this.model;
            var field = this.name;
            var el = this.$el.find("input");
            //Bind input to the model
            el.on("change", function(ev) {
                model.set(field, el.val());
            });
            //And bind the model to the input
            model.on("change:" + field, function(model, value) {
                if (el[0] && el[0].tagName.toLowerCase() == "input")
                    el.val(value);
                else
                    el.html(value);
            });
        },

        bindEvents : function(){
            _.each(this.events, function(callback, ev){

            });
        },

        bind : function(context, model){
            this.unBind();
            this.context = context;
            this.model = model;
            this.model.on("change:" + this.name, this.render, this);
        },
        unBind : function(){
            //this will only work if all events we listen to, we set the scope to this
            if (this.model)
                this.model.offByScope(this);
            delete this.model;
            delete this.context;
        },
        navigate : function(action) {

        }
    }));
}(SUGAR.App));