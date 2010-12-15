Calendar = function() {
	
}

Calendar.setup = function (params) {

    YAHOO.util.Event.onDOMReady(function(){
    	
        var Event = YAHOO.util.Event;
        var Dom = YAHOO.util.Dom;
        var dialog;
        var calendar;
        var showButton = params.button ? params.button : params.buttonObj;
        var userDateFormat = params.ifFormat ? params.ifFormat : params.daFormat;
        var inputField = params.inputField ? params.inputField : params.inputFieldObj;
        var dateFormat = userDateFormat.substr(0,10);
        var pattern = new RegExp("/([-./])/");
        var date_field_delimiter = pattern.exec(dateFormat)[0];
        dateFormat = dateFormat.replace(/[^a-zA-Z]/g,'');
        var monthPos = dateFormat.search(/m/) + 1;
        var dayPos = dateFormat.search(/d/) + 1;
        var yearPos = dateFormat.search(/Y/) + 1;         
        
        Event.on(Dom.get(showButton), "click", function() {

            if (!dialog) {
        
                function closeHandler() {
                    dialog.hide();
                    calendar = null;
                    dialog = null;
                }

                dialog = new YAHOO.widget.Dialog("container", {
                    visible:false,
                    context:[showButton, "tl", "bl"],
                    buttons:[ {text:SUGAR.language.get('app_strings', 'LBL_CLOSE_BUTTON_LABEL'), handler: closeHandler}],
                    draggable:false,
                    close:true
                });
                
                dialog.setHeader(SUGAR.language.get('app_strings', 'LBL_MASSUPDATE_DATE'));
                dialog.setBody('<div id="' + showButton + '_div"></div>');
                dialog.render(document.body);

                dialog.showEvent.subscribe(function() {
                    if (YAHOO.env.ua.ie) {
                        // Since we're hiding the table using yui-overlay-hidden, we 
                        // want to let the dialog know that the content size has changed, when
                        // shown
                        dialog.fireEvent("changeContent");
                    }
                });
                
                // Hide Calendar if we click anywhere in the document other than the calendar
                Event.on(document, "click", function(e) {
                	
                    if(!dialog)
                    {
                       return;	
                    }                	
                	
                    var el = Event.getTarget(e);                   
                    var dialogEl = dialog.element;
                    if (el != dialogEl && !Dom.isAncestor(dialogEl, el) && el != Dom.get(showButton) && !Dom.isAncestor(Dom.get(showButton), el)) {
                        dialog.hide();
                        calendar = null;
                        dialog = null;
                    }
                });                
            }

            // Lazy Calendar Creation - Wait to create the Calendar until the first time the button is clicked.
            if (!calendar) {
            	
                calendar = new YAHOO.widget.Calendar(showButton + '_div', {
                    iframe:false,          // Turn iframe off, since container has iframe support.
                    hide_blank_weeks:true  // Enable, to demonstrate how we handle changing height, using changeContent
                });
                
                calendar.cfg.setProperty('DATE_FIELD_DELIMITER', date_field_delimiter);
                calendar.cfg.setProperty('MDY_DAY_POSITION', dayPos);
                calendar.cfg.setProperty('MDY_MONTH_POSITION', monthPos);
                calendar.cfg.setProperty('MDY_YEAR_POSITION', yearPos);
                
                //Configure the month and days label with localization support where defined
                if(typeof SUGAR.language.languages['app_list_strings']['dom_cal_month_long'] != 'undefined')
                {
                	if(SUGAR.language.languages['app_list_strings']['dom_cal_month_long'].length == 13)
                	{
                	   SUGAR.language.languages['app_list_strings']['dom_cal_month_long'].shift();
                	}
                	calendar.cfg.setProperty('MONTHS_LONG', SUGAR.language.languages['app_list_strings']['dom_cal_month_long']);
                }
                
                if(typeof SUGAR.language.languages['app_list_strings']['dom_cal_day_short'] != 'undefined')
                {
                	if(SUGAR.language.languages['app_list_strings']['dom_cal_day_short'].length == 8)
                	{
                	   SUGAR.language.languages['app_list_strings']['dom_cal_day_short'].shift();
                	}                	
                	calendar.cfg.setProperty('WEEKDAYS_SHORT', SUGAR.language.languages['app_list_strings']['dom_cal_day_short']);
                }
                
                calendar.render();
                
                calendar.selectEvent.subscribe(function() {
                    if (calendar.getSelectedDates().length > 0) {

                        var selDate = calendar.getSelectedDates()[0];
                        var dateArray = new Array();
                        dateArray[monthPos] = selDate.getMonth() + 1; //Add one for month value
                        dateArray[dayPos] = selDate.getDate();
                        dateArray[yearPos] = selDate.getFullYear();
                        
                        selDate = '';
                        for(x in dateArray)
                        {
                        	selDate += date_field_delimiter + dateArray[x];
                        }
                       
                        Dom.get(inputField).value =  selDate.substr(1);
                    } else {
                        Dom.get(inputField).value = "";
                    }
                    dialog.hide();
                });

                calendar.renderEvent.subscribe(function() {
                    // Tell Dialog it's contents have changed, which allows 
                    // container to redraw the underlay (for IE6/Safari2)
                    dialog.fireEvent("changeContent");
                });
            }
            
            var seldate = calendar.getSelectedDates();
            if (Dom.get(inputField).value.length > 0) {
            	calendar.cfg.setProperty("selected", Dom.get(inputField).value);
                seldate = Dom.get(inputField).value.split(date_field_delimiter);       	
            	calendar.cfg.setProperty("pagedate", seldate[monthPos] + calendar.cfg.getProperty("DATE_FIELD_DELIMITER") + seldate[yearPos]);
            	calendar.render();
            } else if (seldate.length > 0) {
                // Set the pagedate to show the selected date if it exists
                calendar.cfg.setProperty("selected", seldate[0]);
                var month = seldate[0].getMonth() + 1;
                var year = seldate[0].getFullYear();
                calendar.cfg.setProperty("pagedate", month + calendar.cfg.getProperty("DATE_FIELD_DELIMITER") + year);
                calendar.render();            	
            }      

            dialog.show();
        });
    });	
}
