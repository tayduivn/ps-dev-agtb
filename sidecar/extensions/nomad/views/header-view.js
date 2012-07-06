(function(app) {

    app.view.views.HeaderView = app.view.View.extend({
        events: {
            'click #moduleList li a': 'onModuleTabClicked',
            'click #createList li a': 'onCreateClicked',
            'click .cube': 'onHomeClicked',
            'click .create-entity': 'onAddClicked'
        },

        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);

            app.events.on("app:view:change", function(layout, params) {
                    this.render(layout, params);
            }, this);
        },

        render: function(layout, params) {
            if (!app.api.isAuthenticated()) {
                this.$el.addClass("hide");
            }
            else {
                this.$el.removeClass("hide");
                app.view.View.prototype._renderHtml.call(this);
                this._renderLeftList(app.template.get('menu-left'),
                    {
                        items: _.keys(app.metadata.getModuleList()),
                        userName: app.user.get('full_name'),
                        userId: app.user.get('id')
                    });

                if (layout === "relationships") {
                    var module = params.parentModule;
                    var id = params.parentModelId;
                    var link = params.link;
                    this._renderRightList(app.template.get('menu-right-relationships'),
                        {
                            createURL: app.nomad.buildLinkRoute(module, id, link, "create?depth=1"),
                            associateURL: app.nomad.buildLinkRoute(module, id, link, "associate?depth=1"),
                            module: params.link
                        });

                } else if (layout === "detail") {
                    this._renderRightList(app.template.get('menu-right-relationships'),
                        {
                            createURL: app.router.buildRoute(params.module, params.modelId) + "/links/create",
                            associateURL: app.router.buildRoute(params.module, params.modelId) + "/links/associate",
                            module: ""
                        });
                }
                else {
                    this._renderRightList(app.template.get('menu-right'), _.keys(app.metadata.getModuleList()));
                }
            }
            return this;
        },

        onCreateClicked: function() {
            $(document.body).removeClass('onR');
        },

        onModuleTabClicked: function() {
            $(document.body).removeClass('onL');
        },

        onHomeClicked: function(e) {
            e.preventDefault();
            $(document.body).toggleClass('onL');
            this.toggleMenu();
        },

        onAddClicked: function(e) {
            e.preventDefault();
            $(document.body).toggleClass('onR');
            this.toggleMenu();
        },

        toggleMenu:function(){
            if($(document.body).hasClass('onL') || $(document.body).hasClass('onR')){
                this.$('.nav-collapse').show();
            }else{
                this.$('.nav-collapse').hide();
            }
        },

        _renderLeftList:function (tmpl,data) {
            if (tmpl) {
                this.$('#moduleList').append(tmpl(data));
            }
        },

        _renderRightList: function(tmpl,data) {
            if (tmpl) {
                this.$('#createList').append(tmpl(data));
            }
        }

    });
})(SUGAR.App);