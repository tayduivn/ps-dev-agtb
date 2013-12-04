// forms switch
function _render_content(view, app) {

    var self = this;

    view.$('select.select2').each(function(){
        var $this = $(this),
            ctor = self.getSelect2Constructor($this);
        $this.select2(ctor);
    });

    view.$('table td [rel=tooltip]').tooltip({
        container:'body',
        placement:'top',
        html:'true'
    });

    view.$('.error input, .error textarea').on('focus', function(){
        $(this).next().tooltip('show');
    });

    view.$('.error input, .error textarea').on('blur', function(){
        $(this).next().tooltip('hide');
    });

    view.$('.add-on')
        .tooltip('destroy')  // I cannot find where _this_ tooltip gets initialised with 'hover', so i detroy it first, -f1vlad
        .tooltip({
            trigger: 'click',
            container: 'body'
    });
}

function _dispose_content(view) {
    view.$('.error input, .error textarea').off('focus');
    view.$('.error input, .error textarea').off('blur');
}
