class pieWedge
{
	//movie clip pointer objects
	var topClip:MovieClip;
	var startSide:MovieClip;
	var endSide:MovieClip;
	var outerEdge:MovieClip;
	var wedgeButton:MovieClip;
	var wedgeHighlight:MovieClip;
	var wedgeShadow:MovieClip;
	var textBox:MovieClip;
	var legendEntry:MovieClip;
	var legendEntryOver:MovieClip;
	var legendEntryBtn:MovieClip;
	var squeezeLabelTextBox:MovieClip; //the squeezed pie wedge group label
	
	var over:Boolean;  //over becomes true when the mouse hovers over this pie wedge
	
	var skip:Boolean;
	var hiddenLabel:Boolean; //the label is hidden while the wedge is inactive due to text overlapping
	var value:Number;  //the number value being represented
	var percent:Number;   //the percent of  value /total values
	var labelText:String; //the text to display next to a pie wedge in place of a numeric value
	var groupName:String;  //the name of the group this wedge represents
	var chartTitle:String; //the title of the new chart if we were to drill down into this pie wedge
	var link:String;  //the link loaded from XML this wedge will redirect to when clicked
	
	var startAngle:Number;
	var midAngle:Number;
	var endAngle:Number;
	var angle:Number;
	var vectorX:Number;
	var vectorY:Number;
	
	var segment:Number;
	var count:Number;
	
	var startFront:Number;
	var endFront:Number;
	var midFront:Number;
	
	var color1:Number;
	var color2:Number;
	var color3:Number;
	
	var sliderLoc:Number;
	
	function pieWedge()
	{
		this.value = 0;
		this.percent = 0;
		this.groupName = "";
		this.color1 = 0xff0000
		this.color2 = 0xee0000
		this.color3 = 0xdd0000
	}
}