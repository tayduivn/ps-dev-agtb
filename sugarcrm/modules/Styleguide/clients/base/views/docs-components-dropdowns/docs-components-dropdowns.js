({
    className: 'container-fluid',

    // components dropdowns
    _renderHtml: function () {
        this._super('_renderHtml');

        this.$('#mm001demo *').on('click.styleguide', function(){ /* make this menu frozen in its state */
            return false;
        });

        this.$('*').on('click.styleguide', function(){
            /* not sure how to override default menu behaviour, catching any click, becuase any click removes class `open` from li.open div.btn-group */
            setTimeout(function(){
                this.$('#mm001demo').find('li.open .btn-group').addClass('open');
            },0.1);
        });
    },

    _dispose: function() {
        this.$('#mm001demo *').off('click.styleguide');

        this._super('_dispose');
    }
})
