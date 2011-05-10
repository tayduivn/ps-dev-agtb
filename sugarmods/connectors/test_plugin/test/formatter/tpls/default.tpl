<div style="visibility:hidden;" id="test_popup_div"></div>
<script type="text/javascript">
function show_ext_rest_test(event)
{literal} 
{

var xCoordinate = event.clientX;
var yCoordinate = event.clientY;
var isIE = document.all?true:false;
      
if(isIE) {
    xCoordinate = xCoordinate + document.body.scrollLeft;
    yCoordinate = yCoordinate + document.body.scrollTop;
}

{/literal}

cd = new CompanyDetailsDialog("test_popup_div", 'This is a test', xCoordinate, yCoordinate);
cd.setHeader("{$fields.{{$mapping_name}}.value}");
cd.display();
{literal}
} 
{/literal}
</script>