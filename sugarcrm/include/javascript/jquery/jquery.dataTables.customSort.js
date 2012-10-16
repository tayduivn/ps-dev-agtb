(function($){
    /**
   	 * Custom sort method for numbers.  Set sSortDataType:"dom-number" to use
   	 */
   	$.fn.dataTableExt.afnSortData['dom-number'] = function  ( oSettings, iColumn )
   	{
   		var aData = [];
        //Use JQuery select on the table cell which has a span with an sfuuid attribute
        $('td:eq('+iColumn+') span[sfuuid]', oSettings.oApi._fnGetTrNodes(oSettings) ).each( function () {
            aData.push(SUGAR.App.currency.unformatAmount(this.textContent, SUGAR.App.user.get('number_grouping_separator'), SUGAR.App.user.get('decimal_separator'), false));
        });
   		return aData;
   	}
})(jQuery);