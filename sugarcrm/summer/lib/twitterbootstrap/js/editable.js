!function ($) {
    	$(function() {
 				// editable demo
 				
 				
        $(".dblclicka").editable('blah.php',{ 
          indicator : 'Saving ...',
          cssclass   : "editable",
          tooltip   : 'Click to edit...'
        })
          $(".dblclick").editable('blah.php',{ 
            indicator : 'Saving ...',
            cssclass   : "editable",
            tooltip   : 'Click to edit...',
            callback : function(value, settings) {
              window.location = index.html;
            }
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
          $(".eStatus").editable('blah.php',{ 
            indicator : 'Saving ...',
            data      : "{'New':'New','Open':'Open','Closed':'Closed','Pending':'Pending'}",
            type      : "select"
          })
          $(".ePriority").editable('blah.php',{ 
            indicator : 'Saving ...',
            data      : "{'Urgent':'Urgent','High':'High','Medium':'Medium', 'Low':'Low'}",
            type      : "select"
          })
    	});
}(window.jQuery)