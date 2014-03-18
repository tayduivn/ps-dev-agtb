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

    // base icons
    _renderHtml: function () {
        this._super('_renderHtml');

        this.$('.chart-icon').each(function(){
            var svg = svgChartIcon($(this).data('chart-type'));
            $(this).html(svg);
        });

        this.$('.filetype-thumbnail').each(function(){
            $(this).html( '<svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" version="1.1" width="28" height="33"><g><path class="ft-ribbon" d="M 0,15 0,29 3,29 3,13 z" /><path d="M 3,1 20.5,1 27,8 27,32 3,32 z" style="fill:#ececec;stroke:#b3b3b3;stroke-width:1;stroke-linecap:butt;" /><path d="m 20,1 0,7 7,0 z" style="fill:#b3b3b3;stroke-width:0" /></g></svg>' );
        });

        this.$('.sugar-cube').each(function(){
            var svg = svgChartIcon('sugar-cube');
            $(this).html(svg);
        });
    }
})
