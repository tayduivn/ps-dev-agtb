class myChartBar
{
	//movie clip pointer objects
	var bar:MovieClip;
	var barButton:MovieClip;
	var barHighlight:MovieClip;

	var textBox:MovieClip;
	var textBoxOver:MovieClip;
	var legendEntry:MovieClip;
	var legendEntryOver:MovieClip;
	var legendEntryBtn:MovieClip;
	
	var skip:Boolean;
	var over:Boolean;  //over becomes true when the mouse hovers over this pie wedge
	var element:Number; //which data entry group this bar is representing
	var group:Number; //
	var value:Number;  //the number value being represented
	var percent:Number;   //the percent of  value /total values
	var labelText:Number;
	var elementName:String;  //the name of the data element this bar represents
	var groupName:String;  //the name of the data group this bar represents (e.g. what year the data element is in)
	var chartTitle:String; //the title of the new chart if we were to drill down into this bar
	var link:String;  //the link loaded from XML this wedge will redirect to when clicked
	
    var left;
	var right;
	var top;
	var bottom;
	
	var color1:Number;
	var color2:Number;
	var color3:Number;
	
	var sliderLoc:Number;
}