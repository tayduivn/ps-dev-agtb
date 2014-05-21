({
    extendsFrom: 'QuickcreateView',

    /**
     * Render HTML only when user not a guest.
     */
    _renderHtml: function(){
        if (_.isEmpty(app.user.id)) {
        	return;
        }
            
        this._super('_renderHtml');
    },
})
