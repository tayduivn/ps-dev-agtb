<style>
{literal}

div .oddListDiv, .evenListDiv
{
    font-size:14px !important;
    display: table-row;
    width: 100%;
}

.oddListDiv:hover div, .evenListDiv:hover div
{
    background-color: #FFF;
}

.oddListDiv
{
    background-color: #FFFFFF;
    background:no-repeat;
}

.evenListDiv
{
    background-color: #F6F6F6;
}

.SEDSearchDisplay
{
    background: none !important; 
    background-image:url(../../../themes/default/images/dcmenugrade.png) !important; 
    background-repeat:repeat-x !important; 
    color:blue !important;
}

.displayEmailLabel, .displayEmailValue 
{
    background-color:#EEEEEE;
    padding:2px;
}
.displayEmailLabel 
{
    color:#999999;
    font-weight:bold;
    text-align:right;
}

.rowLeftDiv
{
    width:20%;
    padding: 0% 0% 0% 0%;
    display: table-cell;
    color:#666666;
    font-size:14;
}

.rowCenterDiv
{
    margin-left: 20%; 
    margin-right: 20%;
    width:55%;
    display: table-cell;
    color:#666666;
    font-size:14;
    font-weight:bold;
}

.rowRightDiv 
{
    width:25%;
    padding: 0% 0% 0% 3%;";
    display: table-cell;
    color:#666666;
}

.divTableContainer
{
    background-color:white;
}

.SEDLoadingDiv
{
    height:100px;
    vertical-align:middle;
    text-align:center;
}

.selectCol
{
    -moz-border-radius-bottomleft:4px;
    -moz-border-radius-bottomright:4px;
    -moz-border-radius-topleft:4px;
    -moz-border-radius-topright:4px;
    background-color:#CCCCCC;
    background-image:url({/literal}{sugar_getimagepath file='listview-select-bg.png'}{literal});
    border:1px solid #B2B2B2;
    padding:1px 4px 3px;
    width: 30px;
}

{/literal}
</style>
	

    <div align="left" width="100%" style="white-space: nowrap;">
        
    </div>
        
   <div class="divTableContainer" id="SEDListView" >
       <div id="SEDListViewContainer" style="display:table">
           {foreach name=rowIteration from=$data item=rowData}
                {if $smarty.foreach.rowIteration.iteration is odd}
        			{assign var='_rowColor' value='oddListDiv'}
        		{else}
        			{assign var='_rowColor' value='evenListDiv'}
        		{/if}
        	<div class="{$_rowColor}">
        		<div class="rowCenterDiv"><p>{$rowData}</p></div>
        	</div> 
        	{/foreach}
        </div>
	</div>
	<div class="sugarFeedDashlet yui-hidden" id="SEDDetailView" ></div>
	

	<div class="SEDLoadingDiv yui-hidden" id="SEDLoadingDiv" style="hieght:300px">
	   <img style="padding-top:50px" src='{sugar_getimagepath file='yui_loading.gif'}' alt='{$navStrings.previous}' align='absmiddle' border='0'>
	</div>
	
