class myGauge
{
	//movie clip pointer objects
	var rangeMC:MovieClip;
	var rangeMask:MovieClip;
	var rangeHighlight:MovieClip;
	var rangeHighlightMask:MovieClip;
	var rangeButton:MovieClip;
	var rangeButtonMask:MovieClip;
	var textBox:MovieClip;

	var group:Number; //the name of this gauge range
	var percent:Number;   //the percent of  value /total values
	var labelText:String;
	var groupName:String;  //the name of the data group this bar represents (e.g. what year the data element is in)
	var link:String;  //the link loaded from XML this wedge will redirect to when clicked
	var subGroupXML:XML;  //sub group info stored in an XML organization
	
	var startValue:Number;
	var endValue:Number;
	
	var color1:Number;
	var color2:Number;
	var color3:Number;
	var color4:Number;
	
	var sliderLoc:Number;
}