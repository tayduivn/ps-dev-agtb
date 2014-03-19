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
