/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
({
    className: 'container-fluid',

    // forms switch
    _renderHtml: function () {
        this._super('_renderHtml');

        var self = this;

        this.$('select.select2').each(function(){
            var $this = $(this),
                ctor = self.getSelect2Constructor($this);
            $this.select2(ctor);
        });

        this.$('table td [rel=tooltip]').tooltip({
            container:'body',
            placement:'top',
            html:'true'
        });

        this.$('.error input, .error textarea').on('focus', function(){
            $(this).next().tooltip('show');
        });

        this.$('.error input, .error textarea').on('blur', function(){
            $(this).next().tooltip('hide');
        });

        this.$('.add-on')
            .tooltip('destroy')  // I cannot find where _this_ tooltip gets initialised with 'hover', so i detroy it first, -f1vlad
            .tooltip({
                trigger: 'click',
                container: 'body'
        });
    },

    _dispose: function() {
        this.$('.error input, .error textarea').off('focus');
        this.$('.error input, .error textarea').off('blur');
    },

    getSelect2Constructor: function($select) {
        var _ctor = {};
        _ctor.minimumResultsForSearch = 7;
        _ctor.dropdownCss = {};
        _ctor.dropdownCssClass = '';
        _ctor.containerCss = {};
        _ctor.containerCssClass = '';

        if ( $select.hasClass('narrow') ) {
            _ctor.dropdownCss.width = 'auto';
            _ctor.dropdownCssClass = 'select2-narrow ';
            _ctor.containerCss.width = '75px';
            _ctor.containerCssClass = 'select2-narrow';
            _ctor.width = 'off';
        }

        if ( $select.hasClass('inherit-width') ) {
            _ctor.dropdownCssClass = 'select2-inherit-width ';
            _ctor.containerCss.width = '100%';
            _ctor.containerCssClass = 'select2-inherit-width';
            _ctor.width = 'off';
        }

        return _ctor;
    }
})
