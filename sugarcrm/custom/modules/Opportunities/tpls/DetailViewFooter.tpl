{{include file='include/DetailView/footer.tpl'}}

      <!-- End the DetailView -->
    </div>
	<div id="outerTab2">
		{php}

		$opp = new Opportunity;
		$opp->retrieve($_REQUEST['record']);

        echo "<iframe style='border-width: 0px;' width='100%' height='500' id='isp_oppWall'></iframe>

        <script>
		function oppWallInit() {
	        iframe = document.getElementById('isp_oppWall');

            if (iframe.src == '') {
            		document.getElementById('isp_oppWall').src = '" . IBMHelper::$isp_base_url . "?page=oppWall&user={$GLOBALS['current_user']->email1}&opp={$opp->name}';
            }
		}
        </script>";

		{/php}
	</div>
    <div id="outerTab3">
      {php}
      include('modules/Opportunities/RoadmapViewer.php');
      {/php}
    </div>
  </div>
</div>
