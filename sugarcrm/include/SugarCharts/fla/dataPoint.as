class dataPoint
{
	//movie clip pointer objects
	var pointMC:MovieClip;
	var pointButton:MovieClip;
	var pointGlow:MovieClip;

	var line:Number; //which data entry group this point is representing (i.e. what line this point is on)
	var year:Number; //the index on the point in its parent line class
	var value:Number;  //the number value being represented
	var percent:Number;   //the percent of  value /total values
	var valueText:Number;
	var left:Number; //the x coordinate of the data point
	
	var skip:Boolean;
	var yearName:String;  //the name of the data group thispoint represents (e.g. what year the data element is in)
	var link:String;  //the link loaded from XML this wedge will redirect to when clicked
	var subGroupXML:XML;  //sub group info stored in an XML organization
	
	var color1:Number;
	
	var sliderLoc:Number;
}