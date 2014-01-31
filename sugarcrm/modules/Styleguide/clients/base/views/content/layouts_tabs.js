// layouts tabs
function _render_content(view, app) {
    view.$('#nav-tabs-pills')
        .find('ul.nav-tabs > li > a, ul.nav-list > li > a, ul.nav-pills > li > a')
        .on('click.styleguide', function(e){
            e.preventDefault();
            e.stopPropagation();
            $(this).tab('show');
        });
}

function _dispose_content(view) {
    view.$('#nav-tabs-pills')
        .find('ul.nav-tabs > li > a, ul.nav-list > li > a, ul.nav-pills > li > a')
        .off('click.styleguide');
}
