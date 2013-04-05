({
    initialize: function(options) {
        var self = this;
        app.view.View.prototype.initialize.call(this, options);
        app.events.on("app:sync:complete", function() {
            $('.s7icontainer').show();
        }, this);
        app.events.on("app:login:success", self.render, this);
        $('body').prepend(this.template());
        $('.s7icontainer').hide();
        $('.s7icontainer .span4.hasChildren:not(.active)').on('click', function(e){
            s7iShowArticle(this);
            e.stopPropagation();
        });

        $('.s7icontainer article aside').on('click', function(e){
            s7iReturnHome();
            e.stopPropagation();
        });

        $('.s7iclose').on('click', function(e){
            $('.sugar7intro.btn').trigger('click');
            e.preventDefault();
        });

        $('.s7i_home').on('click', function(e){
            s7iReturnHome();
            return false;
        });

        $('.trigger_dashboard').on('click', function(){
            $('a[data-route=#Home]').trigger('click');
            $('.sugar7intro.btn').trigger('click');
            return false;
        });

        $('.trigger_accounts').on('click', function(){
            $('a[data-route=#Accounts]').trigger('click');
            $('.sugar7intro.btn').trigger('click');
            return false;
        });

        $('.trigger_leads').on('click', function(){
            $('a[data-route=#Leads]').trigger('click');
            $('.sugar7intro.btn').trigger('click');
            return false;
        });
        function s7iShowArticle(el) {
            var p = $(el).position();
            $(el).addClass('active').find('article').css({'left':p.left, 'top':p.top}).animate({'height':'100%','width':'100%', 'left':'0', 'top':'0', 'opacity':'1'},200).addClass('visible');
        }

        function s7iReturnHome() {
            $('article.visible').animate({'width':'1%', 'height':'1%', 'left':'50%', 'top': '45%', 'opacity': '0'},300, function(){$(this).css({'height':'.01%', 'width': '.01%'}).removeClass('visible');}).closest('.span4').removeClass('active');
        }
    },
    _renderHtml: function(){
        if ($('.sugar7intro.btn').length == 0) {
            $('#footer .btn-toolbar .btn-group').prepend('<a href="javascript:void(0);" class="sugar7intro btn btn-invisible" ><i class="icon-eye-open"></i> Preview<sup>Sugar7</sup></a>');
            $('.sugar7intro.btn').toggleClass('active');
            $('.sugar7intro').on('click', function(e){
                $('.s7icontainer').toggle();
                $('.sugar7intro.btn').toggleClass('active');
                return false;
            });
        }
    }
})

