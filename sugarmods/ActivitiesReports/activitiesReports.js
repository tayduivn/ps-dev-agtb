/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
YAHOO.widget.DataTable.prototype.getColumn = function(column) {
    var oColumn = this._oColumnSet.getColumn(column);

    if(!oColumn) {
        // Validate TD element
        var elCell = column.nodeName.toLowerCase() != "th" ? this.getTdEl(column) : false;
        if(elCell) {
            oColumn = this._oColumnSet.getColumn(elCell.cellIndex);
        }
        // Validate TH element
        else {
            elCell = this.getThEl(column);
            if(elCell) {
                // Find by TH el ID
                var allColumns = this._oColumnSet.flat;
                for(var i=0, len=allColumns.length; i<len; i++) {
                    if(allColumns[i].getThEl().id === elCell.id) {
                        oColumn = allColumns[i];
                    } 
                }
            }
        }
    }
    if(!oColumn) {
        YAHOO.log("Could not get Column for column at " + column, "info", this.toString());
    }
    return oColumn;
};
function success(o) {
	var results = eval(o.responseText);
	var myConfigs = {   
			paginator : new YAHOO.widget.Paginator({   
		         rowsPerPage:50  
		    })   
		 };   
	var myColumnDefs = [   
	    {key:"type", label:SUGAR.language.get("Activities", "LBL_TYPE"),sortable:true, resizeable:true, width:150},   
	    {key:"url", label:SUGAR.language.get("Activities", "LBL_SUBJECT"),sortable:true, resizeable:true, width:350},   
	    {key:"date_start", label:SUGAR.language.get("Activities", "LBL_LIST_DATE"),sortable:true,resizeable:true, width:150},   
	    {key:"status", label:SUGAR.language.get("Activities", "LBL_STATUS"),sortable:true, resizeable:true, width:100}   
    ];   
              
    var myDataSource = new YAHOO.util.DataSource(results);   
    myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;   
    myDataSource.responseSchema = {fields: ["type","url","date_start","status"]};   
  
   var myDataTable = new YAHOO.widget.DataTable("activitiesDiv", myColumnDefs, myDataSource, myConfigs);   
               
    return {   
        oDS: myDataSource,   
        oDT: myDataTable   
    };   
}
