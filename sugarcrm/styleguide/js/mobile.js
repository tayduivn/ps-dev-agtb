// Make links keep in app 
(function(a,b,c){if(c in b&&b[c]){var d,e=a.location,f=/^(a|html)$/i;a.addEventListener("click",function(a){d=a.target;while(!f.test(d.nodeName))d=d.parentNode;"href"in d&&(chref=d.href).replace(e.href,"").indexOf("#")&&(!/^[a-z\+\.\-]+:/i.test(chref)||chref.indexOf(e.protocol+"/"+e.host)===0)&&(a.preventDefault(),e.href=d.href)},!1)}})(document,window.navigator,"standalone")

!function ($) {
  $(function(){
    
    if ( $(window).width() < 768) {		
      // trigger the module menu
      $('.cube').click(function () {
        $('html').find('body').toggleClass('onL');
        return false;
      })

      // trigger the module menu
      $('.launch').click(function () {
        $('html').find('body').toggleClass('onR');
        return false;
      })
    }

    // toggle stars (needs tap logic for mobile)
    $('article').find('[class^=icon-star]').on('click', function () {
      $(this).toggleClass('icon-star icon-star-empty');
    })
    $('article [id^=listing-action]').find('.grip').on('click', function () {
      $(this).parent().find('span').toggleClass('hide on');
      $(this).toggleClass('on');
    })
    
    // search toggle
    $('.navbar').find('#search').on('click', function () {
      $('body').find('#searchForm').toggleClass('hide');
      return false;
    })
    $('#searchForm').find('.cancel').on('click', function () {
      $('body').find('#searchForm').toggleClass('hide');
      return false;
    })

    // fake hide of message for prototype
    setTimeout(function() {
      $(".alert").fadeOut();
    }, 3600);

    // fake hide of record for prototype
    $('article').find('[class^=icon-remove]').on('click', function () {
      $(this).parent().remove();
      $('container-fluid').html('<div class="top alert alert-danger alert-block">Opportunity has been removed.</div>');
    })
  })
}(window.jQuery)
