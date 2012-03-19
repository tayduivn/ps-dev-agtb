!function ($) {
  $(function(){
		$('.cube').click(function () {
      $('html').find('body').toggleClass('onL');
    		return false;
		})
    // toggle stars (needs tap logic for mobile)
    $('article').find('[class^=icon-star]').on('click', function (e) {
			$(this).toggleClass('icon-star icon-star-empty');
    })
    $('article').find('[id^=listing-action] .grip').on('click', function (e) {
			$(this).parent().find('span').toggleClass('hidden on');
			$(this).toggleClass('on');
    })
  })
}(window.jQuery)