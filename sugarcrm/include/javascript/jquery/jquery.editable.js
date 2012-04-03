!function ($) {
    	$(function() {
 				// editable demo
          $(".dblclick").editable('blah.php',{ 
            indicator : 'Saving ...',
            cssclass   : "editable",
          })
          $(".edit_select").editable('blah.php',{ 
            indicator : 'Saving ...',
            data      : "{'Urgent':'Urgent','High':'High','Medium':'Medium', 'selected':'Low'}",
            type      : "select",
            cssclass   : "editable",
            submitdata : function() {
              return {id : 2};
            }
          })

    	});
}(window.jQuery)