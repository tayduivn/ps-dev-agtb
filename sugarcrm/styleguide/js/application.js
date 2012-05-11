!function ($) {
  $(function(){

    // fix sub nav on scroll
    var $win = $(window)
      , $nav = $('.subnav')
      , navTop = $('.subnav').length && $('.subnav').offset().top - 40
      , isFixed = 0

    processScroll()
    $win.on('scroll', processScroll)

    function processScroll() {
      var i, scrollTop = $win.scrollTop()
      if (scrollTop >= navTop && !isFixed) {
        isFixed = 1
        $nav.addClass('subnav-fixed')
      } else if (scrollTop <= navTop && isFixed) {
        isFixed = 0
        $nav.removeClass('subnav-fixed')
      }
    }

    // do this if greater than 768px page width
    if ( $(window).width() > 768) {		
    // tooltip demo
    $('body').tooltip({
      delay: { show: 400 },
      selector: "[rel=tooltip]"
    })
    $('table').tooltip({
      selector: "[rel=tooltip]"
    })
    $('.thumbnail').tooltip({
      selector: "[rel=tooltip]",
			placement: "bottom"
    })
    $('.navbar, .subnav').tooltip({
      selector: "[rel=tooltip]",
			placement: "bottom"
    })  
    }
   
   $("th:contains('Subject')").css("width","60%")
   $("th:contains('Name'),th:contains('Opportunity')").css("width","40%")
   $("th:contains('Modified'),th:contains('Created'),th:contains('Number'),th:contains('ID'),th:contains('input')").css("width","1%")
   

      
    // keybinding
    $(document).keyup(function(e){
        if(e.keyCode === 27) 
          $(".alert-top").remove();
    })
    
    // make code pretty (styleguide only)
    window.prettyPrint && prettyPrint()

    // add tipsies to grid for scaffolding (styleguide only)
    if ($('#grid-system').length) {
      $('#grid-system').tooltip({
          selector: '.show-grid > div'
        , title: function () { return $(this).width() + 'px' }
      })
    }

    // toggle all stars
    $('.toggle-all-stars').on('click', function (e) {
    		$(this).closest('table').toggleClass('active'); 
    		return false;
    })

    // toggle all checkboxes
    $('.toggle-all').on('click', function (e) {
    		$('table').find(':checkbox').attr('checked', this.checked);      
    })
    
    // timeout the alerts
    setTimeout(function(){$('.timeten').fadeOut().remove();},9000)

    // toggle star
    $('.icon-star').on('click', function (e) {
    		$(this).parent().toggleClass('active');
    		return false;  
    })

    // toggle more hide
    $('.more').toggle(
      function (e) {
    		$(this).parent().prev('.extend').removeClass('hide');
    		$(this).html('Less &nbsp;<i class="icon-chevron-up"></i>');
    		return false;  
      },
      function (e) {
      		$(this).parent().prev('.extend').addClass('hide');
      		$(this).html('More &nbsp;<i class="icon-chevron-down"></i>');
      		return false;  
    })

    //popover
    $("[rel=popover]").popover()
    $("[rel=popoverTop]").popover({placement: "top"})
      
    //clickovers  
    $('[rel="clickover"]').clickover() 
    $('[rel="clickoverTop"]').clickover({placement: "top"})

    
    // column collapse
    $('.drawerTrig').on('click',
    function () {
      $(this).toggleClass('pull-right').toggleClass('pull-left');
      $(this).find('i').toggleClass('icon-chevron-left').toggleClass('icon-chevron-right');
      $('#drawer').toggleClass('span2');
      $('.bordered').toggleClass('hide');
      $('#charts').toggleClass('span10').toggleClass('span12');
      return false;
    })

		// tour
    $('#tour').on('click', function (e) {
			$('.pointsolight').prependTo('body');
    })
    
    $('.btngroup .btn').button()


    // editable example
    $('.dblclick').hover(
      function () {$(this).before('<span class="span2" style="position: absolute; left: -7px; width: 15px"><i class="icon-pencil icon-sm"></i></span>');},
      function () {$('span.span2').remove();}
  	)
  })
  
  $('.actions').find('a[data-toggle=tab]').on('click', function (e) {
    $('.nav-tabs').find('li').removeClass('on');
		$(this).parent().parent().addClass('on');
  })
    
    // remove a close item
    $('#folded').find('[data-toggle=tab]').on('click', function (e) {
			$('.nav-tabs').find('li').removeClass('on');
    })
  
  // toggle module search (needs tap logic for mobile)
	$('.addit').on('click', function () {
	    $(this).toggleClass('active');
	    $(this).parent().parent().parent().find('.form-addit').toggleClass('hide');
	    return false;
	})
	$('.search').on('click', function () {
	    $(this).toggleClass('active');
	    $(this).parent().parent().parent().find('.dataTables_filter').toggle(
	      function () {
	        $(this).find('input').focus();
  	  });
  	  $(this).parent().parent().parent().find('.form-search').toggle(
	      function () {
	        $(this).find('input').focus();
  	  });
	    $(this).parent().parent().parent().find('.form-search').toggleClass('hide');
	    return false;
	})
  $('#moduleTwitter.filtered input').quicksearch('#moduleTwitter article')
  $('#moduleLog.filtered input').quicksearch('#moduleLog article')
  $('#moduleRelated.filtered input').quicksearch('#moduleRelated article')
  $('#moduleActivity.filtered input').quicksearch('#moduleActivity article')
  $('#moduleActivity.filtered input').quicksearch('#moduleActivity .results li')
 
  // DATATABLE
  //--------------
  $('table.datatable').dataTable({
    "bPaginate": false,
    "bFilter": true,
    "bInfo": false,
    "bAutoWidth": true
  })

	/* Default class modification */
	$.extend( $.fn.dataTableExt.oStdClasses, {
		"sSortAsc": "header headerSortDown",
		"sSortDesc": "header headerSortUp",
		"sSortable": "header"
	} );

	/* API method to get paging information */
	$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
	{
		return {
			"iStart":         oSettings._iDisplayStart,
			"iEnd":           oSettings.fnDisplayEnd(),
			"iLength":        oSettings._iDisplayLength,
			"iTotal":         oSettings.fnRecordsTotal(),
			"iFilteredTotal": oSettings.fnRecordsDisplay(),
			"iPage":          Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
			"iTotalPages":    Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
		};
	}

	/* Bootstrap style pagination control */
	$.extend( $.fn.dataTableExt.oPagination, {
		"bootstrap": {
			"fnInit": function( oSettings, nPaging, fnDraw ) {
				var oLang = oSettings.oLanguage.oPaginate;
				var fnClickHandler = function ( e ) {
					e.preventDefault();
					if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
						fnDraw( oSettings );
					}
				};

				$(nPaging).addClass('pagination').append(
					'<ul>'+
						'<li class="prev disabled"><a href="#">&larr; '+oLang.sPrevious+'</a></li>'+
						'<li class="next disabled"><a href="#">'+oLang.sNext+' &rarr; </a></li>'+
					'</ul>'
				);
				var els = $('a', nPaging);
				$(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
				$(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
			},

			"fnUpdate": function ( oSettings, fnDraw ) {
				var iListLength = 5;
				var oPaging = oSettings.oInstance.fnPagingInfo();
				var an = oSettings.aanFeatures.p;
				var i, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

				if ( oPaging.iTotalPages < iListLength) {
					iStart = 1;
					iEnd = oPaging.iTotalPages;
				}
				else if ( oPaging.iPage <= iHalf ) {
					iStart = 1;
					iEnd = iListLength;
				} else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
					iStart = oPaging.iTotalPages - iListLength + 1;
					iEnd = oPaging.iTotalPages;
				} else {
					iStart = oPaging.iPage - iHalf + 1;
					iEnd = iStart + iListLength - 1;
				}

				for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
					// Remove the middle elements
					$('li:gt(0)', an[i]).filter(':not(:last)').remove();

					// Add the new list items and their event handlers
					for ( j=iStart ; j<=iEnd ; j++ ) {
						sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
						$('<li '+sClass+'><a href="#">'+j+'</a></li>')
							.insertBefore( $('li:last', an[i])[0] )
							.bind('click', function (e) {
								e.preventDefault();
								oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
								fnDraw( oSettings );
							} );
					}

					// Add / remove disabled classes from the static elements
					if ( oPaging.iPage === 0 ) {
						$('li:first', an[i]).addClass('disabled');
					} else {
						$('li:first', an[i]).removeClass('disabled');
					}

					if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
						$('li:last', an[i]).addClass('disabled');
					} else {
						$('li:last', an[i]).removeClass('disabled');
					}
				}
			}
		}
	} );

	/* Table initialisation */
	$(document).ready(function() {
		$('.datatable').dataTable( {
			"sDom": "<'row'<'span8'l><'span8'f>r>t<'row'<'span8'i><'span8'p>>",
			"sPaginationType": "bootstrap",
			"oLanguage": {
				"sLengthMenu": "_MENU_ records per page"
			}
		} );
	} );
  // Select widget
  $(".chzn-select").chosen()
  $(".chzn-select-deselect").chosen({allow_single_deselect:true})
  
}(window.jQuery)