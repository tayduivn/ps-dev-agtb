(function(app) {

    app.view.ClickToEditField = function (field, view) {
        this.field = field;
        this.view = view;
        return this.render();
    };

    app.view.ClickToEditField.prototype.render = function() {
        this._addCTEIcon(this.field);

        this.field.$el.editable(function(value, settings){
                return value;
            },
            {
                select: true,
                field: this.field,
                view: this.view,
                onedit:function(settings, original){
                    // hold value for use later in case user enters a +/- percentage
                    if (settings.field.type == "int"){
                        settings.field.holder = $(original).html();
                    }
                },
                callback: function(value, settings) {
                    try{
                        // if it's an int, and the user entered a +/- percentage, calculate it
                        if(settings.field.type == "int"){
                            orig = settings.field.holder;
                            if(value.match(/^[+-][0-1]?[0-9]?[0-9]%$/)) {
                                value = eval(orig + value[0] + "(" + value.substring(1,value.length-1) / 100 + "*" + orig +")");
                            } else if (!value.match(/^[0-9]*$/)) {
                                value = orig;
                            }
                        }
                        
                        settings.field.model.set(settings.field.name, value);
                        settings.field.model.set("timeperiod_id", settings.field.context.forecasts.get("selectedTimePeriod").id);
                        settings.field.model.set("current_user", app.user.get('id'));
                        settings.field.model.url = settings.view.url + "/" + settings.field.model.get("id");
                        settings.field.model.save(settings.field.name, value);
                    } catch (e) {
                        app.logger.error('Unable to save model in forecastsWorksheet.js: _renderClickToEditField - ' + e);
                    }
                    return value;
                }
            }
        );
        return this.field;
    };

    app.view.ClickToEditField.prototype._addCTEIcon = function(){
        // add icon markup
        this.field.cteIcon = $('<span class="span2" style=" border-right: medium none; position: absolute; left: -5px; width: 15px"><i class="icon-pencil icon-sm"></i></span>');

        // add events
        this.field.showCteIcon = function(){
            this.$el.parent().css('overflow-x', 'visible');
            this.$el.before(this.cteIcon);
        };

        this.field.hideCteIcon = function(){
            this.$el.parent().find(this.cteIcon).detach();
            this.$el.parent().css('overflow-x', 'hidden');
        };

        var events = this.field.events || {};
        this.field.events = _.extend(events, {
            'mouseenter': 'showCteIcon',
            'mouseleave': 'hideCteIcon'
        });
        this.field.delegateEvents();
    };

})(SUGAR.App);