class myDataGroup
{	
	//group data
	var values //values
	var links:String //the links from the  XML file
	var titles:String //the title/name of each group from the XML file	
	var chartTitle:String //the title for the chart if it is drilled down into this group
	var labelText:String; //label texts for each group
	var hasSubGroups:Boolean; //true if there are any subgroups in the array
	var subGroups:Array; //the sub groups of each group
}