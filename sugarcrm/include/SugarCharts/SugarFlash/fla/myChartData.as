class myChartData
{	
	var thumbnail:MovieClip //thumbnail at top of page
	var chartXML:XML //the xml data loaded from input file
	
	//group data
	var groups:Array //array of data group objects

	//chart properties
	var chartType:String; //what type of chart (pie chart, group by chart, etc)
	var chartTitle:String; //the title of the chart
	var subTitle:String; //the text to go in the "units" text box
	var legendOn:Boolean; //the default state of the legend
	var display:Number; //display mode (0=off, 1=value, 2=percent)
	var numGroups:Number; //the number of children in the "data" node of the XML
	
	//y-axis data
	var yAxisOverride:Boolean; //sets to true if y-axis data is provided in the XML data
	var yMin:Number; //minimum y-axis range display
	var yMax:Number; //maximum y-axis range display (ideal value is one y-step more than the max value in the chart)
	var yStep:Number; //y-step is the interval at which y-axis labels are drawn starting with yMin and going no more than yMax 
	var yLog:Number; //yLog value for logarithmic scales (this feature is not currently implmeneted)
	
	//input file
	var inputFile:String; //a PHP or XML file that provides the chart data to be displayed
}