({
    events: {
        'click #tour': 'systemTour',
        'click #print': 'print',
        'click #top': 'top',
        'click #languageList .dropdown-menu a' : 'setLanguage'
    },
    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.events.on("app:sync:complete", this.systemTour, this);
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
        app.view.View.prototype.initialize.call(this, options);

        // Format the list of languages for the template
        var languages = app.lang.getAppListStrings('available_language_dom');
        this.languageList = [];
        for (var languageKey in languages) {
            if (languageKey !== "")
            this.languageList.push({
                key: languageKey,
                value: languages[languageKey]
            })
        }
    },
    _renderHtml: function() {
        this.isAuthenticated = app.api.isAuthenticated();
        this.currentLang = app.lang.getLanguage() || "en_us";

        if (app.config && app.config.logoURL) {
            this.logoURL=app.config.logoURL;
        }
        app.view.View.prototype._renderHtml.call(this);
    },
    systemTour: function() {

        //set up bouncing arrows
        var arrows=new Array();
        arrows[0] = {
            target: "#moduleList li.Cases",
            placement: "bottom"
        };
        arrows[1] = {
            target: "input.search-query",
            placement: "bottom"
        };
        arrows[2] = {
            target: "#createList",
            placement: "bottom right",
            leftOffset: 40,
            topOffset: -10
        };
        arrows[3] = {
            target: "#tour",
            placement: "top"
        };


        for(var i=0; i<arrows.length; i++) {



            this.$(arrows[i].target).ready(function(){

                var direction,bounce;
                if (arrows[i].placement == "top right") {
                    bounce = "up right";
                    direction = "down right"
                } else if (arrows[i].placement == "top left") {
                    bounce = "up left";
                    direction = "down left"
                } else if(arrows[i].placement == "top") {
                    bounce = "up";
                    direction = "down"
                } else if (arrows[i].placement == "bottom right") {
                    bounce = "down right";
                    direction = "up right"
                } else if (arrows[i].placement == "bottom left") {
                    bounce = "down left";
                    direction = "up left"
                } else {
                    bounce = "down";
                    direction = "right"
                }

                $(arrows[i].target).popoverext({
                    title: "",
                    content: "arrow",
                    footer: "",
                    placement: arrows[i].placement,
                    id: false,
                    fixed: true,
                    trigger: 'manual',
                    template: '<div class="popover arrow"><div class="pointer ' +direction+'"></div></div>',
                    onShow:  function(){
                        $('.pointer').css('top','0px');

                        $(".popover .pointer")
                            .effect("custombounce", { times:1000, direction: bounce, distance: 50, gravity: false }, 2000,
                            function(){

//                                    $('.pointer').attr('style','');

                            }
                        );
                    },
                    leftOffset: arrows[i].leftOffset ? arrows[i].leftOffset : 0,
                    topOffset: arrows[i].topOffset ? arrows[i].topOffset : 0,
                    hideOnBlur: false

                });
            });
            //empty popover div and insert arrow
            $(arrows[i].target+"Popover").empty().html("arrow");

        }


        //show modal
        centerModal();
        this.$('#systemTour').modal({"backdrop":"static"});
        this.$('#systemTour').modal('show');
        $('div.modal-backdrop').css('opacity',0.2);

        //wire up buttons in modal
        this.$("#systemTour .screen .done").click(function() {
            $('#systemTour').modal('hide');
        });

        this.$("#systemTour .screen .next").each(function(index){
            var screenId = "#screen" + (index+1);
            var nextScreenId = "#screen" + (index+2);
                $(this).click(function() {
                    $('#systemTour '+screenId).toggleClass("hide");
                    $('#systemTour '+nextScreenId).toggleClass("hide");
                    if(index > 0) {
                        $(arrows[index-1].target).popoverext('hide');
                    }
//                    if(index > 0) {
                        $(arrows[index].target).popoverext('show');
//                    }

                    if(index == 0) {
                        centerModal();
                    }
                });
        });

        this.$("#systemTour .screen .back").each(function(index){
            var screenId = "#screen" + (index+2);
            var prevNum = (index+1);
            var prevScreenId = "#screen" + (index+1);
                $(this).click(function() {
                    $('#systemTour '+screenId).toggleClass("hide");
                    $('#systemTour '+prevScreenId).toggleClass("hide");
                    if(index > 0) {
                        $(arrows[index-1].target).popoverext('show');
                    }
                    $(arrows[index].target).popoverext('hide');
                    if(index == 0) {
                        centerModal();
                    }
                });


        });

        this.$("#systemTour .screen .skip").each(function(index){
            var screenId = "#screen" + (index+1);
            var totalScreens = $("#systemTour .screen").length,
                lastScreenId = "#screen" +totalScreens;


            $(this).click(function() {
                $('#systemTour '+screenId).toggleClass("hide");
                $('#systemTour '+lastScreenId).toggleClass("hide");
                centerModal()
            });

        });



        function centerModal() {
            $("#systemTour").css("left",$(window).width()/2 - $("#systemTour").width()/2);
            $("#systemTour").css("margin-top",-$("#systemTour").height()/2);
        }

    },
    print: function() {
        window.print();
    },
    top: function() {
        scroll(0,0);
    },
    setLanguage: function(e) {
        app.lang.hasChanged = true;
        var $li = this.$(e.currentTarget),
            langKey = $li.data("lang-key");
        app.alert.show('language', {level: 'warning', title: 'LBL_LOADING_LANGUAGE', autoclose: false});
        app.lang.setLanguage(langKey, function() { app.alert.dismiss('language'); });
    }
})
