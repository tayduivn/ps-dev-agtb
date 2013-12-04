// components dropdowns
function _render_content(view, app) {
    view.$('#mm001demo *').on('click.styleguide', function(){ /* make this menu frozen in its state */
        return false;
    });

    view.$('*').on('click.styleguide', function(){
        /* not sure how to override default menu behaviour, catching any click, becuase any click removes class `open` from li.open div.btn-group */
        setTimeout(function(){
            view.$('#mm001demo').find('li.open .btn-group').addClass('open');
        },0.1);
    });
}

function _dispose_content(view) {
    view.$('#mm001demo *').off('click.styleguide');
}
