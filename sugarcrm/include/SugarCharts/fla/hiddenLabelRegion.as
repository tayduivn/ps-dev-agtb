class hiddenLabelRegion
{
	//movie clip pointer object
	var textBox:MovieClip;
	
	var startAngle:Number;
	var midAngle:Number;
	var endAngle:Number;
	var midFront:Number;
	var startWedgeIndex:Number;
	var endWedgeIndex:Number;
	
	var combinedValues:Number;
	var combinedPercents:Number;
	var numWedges:Number;
	var prefix:String;
	var postfix:String;
	var sections:String;
	
	function hiddenLabelRegion()
	{
		startAngle = 0;
		midAngle = 0;
		endAngle = 0;
		combinedValues = 0;
		numWedges = 0;
		prefix = "";
		postfix = "";
		sections = "sections"
	};
	
	function getValueLabel():String
	{
		return "<span class='squeezedWedgeLabels'>" + prefix + combinedValues + postfix + "</span><br /><span class='squeezedWedgeLabels'>(" + numWedges + " " + sections + ")" + "</span>";
	};
	
	function getPercentLabel():String
	{
		return "<span class='squeezedWedgeLabels'>" + combinedPercents + "%</span><br /><span class='squeezedWedgeLabels'>(" + numWedges + " " + sections + ")" + "</span>";
	};
	function getBlankLabel():String
	{
		return "<span class='squeezedWedgeLabels'>(" + numWedges + " " + sections + ")" + "</span>";
	};
}