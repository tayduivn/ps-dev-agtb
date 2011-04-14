class myFunnel
{
	//movie clip pointer objects
	var segment:MovieClip;
	var segmentB:MovieClip;
	var segmentButton:MovieClip;
	var textButton:MovieClip;
	var segmentHighlight:MovieClip;

	var textBox:MovieClip;
	var textBox2:MovieClip;
	var textBoxOver:MovieClip;
	var textBoxOver2:MovieClip;
	var legendEntry:MovieClip;
	var legendEntryOver:MovieClip;
	var legendEntryBtn:MovieClip;
	
	var over:Boolean;  //over becomes true when the mouse hovers over this pie wedge
	var group:Number; //which data entry group this bar is representing
	var value:Number;  //the number value being represented
	var percent:Number;   //the percent of  value /total values
	var labelText:String;
	var groupName:String;  //the name of the data group this funnel segment represents
	var chartTitle:String; //the title of the new chart if we were to drill down into this funnel segment
	var link:String;  //the link loaded from XML this wedge will redirect to when clicked
	var subGroupXML:XML;  //sub group info stored in an XML organization
	
    var top:Number;
	var middle:Number;
	var bottom:Number;
	var radiusXtop:Number;
	var radiusYtop:Number;
	var radiusXmiddle:Number;
	var radiusXbottom:Number;
	var radiusYbottom:Number;
	
	var color1:Number;
	var color2:Number;
	var color3:Number;
	var color4:Number;
	
	var sliderLoc:Number;
}