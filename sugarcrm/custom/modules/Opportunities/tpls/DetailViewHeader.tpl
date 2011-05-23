<script type="text/javascript" src="{sugar_getjspath file='include/javascript/sugar_grp_yui_widgets.js'}"></script>
<script type="text/javascript" src="custom/modules/Opportunities/IBM_Opportunities.js"></script>
<script type="text/javascript">
var  DetailViewOutertabs = new YAHOO.widget.TabView("DetailViewOuter_tabs");
</script> 

<div id="DetailViewOuter_tabs" class="yui-navset">
  <ul class="yui-nav">
    <li id="outerTabButton1" class="selected"><a href="#outerTab1"><em>Opportunity</em></a></li>
    <li id="outerTabButton2" onClick="oppWallInit();"><a href="#outerTab2"><em>Opportunity Wall</em></a></li>
    <li {{if $smarty.request.selected_tab == 'roadmapping_tab'}}class="selected"{{/if}} id="outerTabButton3"><a href="#outerTab3"><em>Roadmapping</em></a></li>
  </ul>
  <div class="yui-content">
    <div id="outerTab1">
      <!-- Begin the DetailView -->

{{include file='include/DetailView/header.tpl'}}
