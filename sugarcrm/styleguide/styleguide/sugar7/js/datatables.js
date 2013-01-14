
$(document).ready(function() {

  // preview previous/next record in table
  $('.previous-row,.next-row').live('click', function() {
      var cTr = $('#example tr.highlighted.current'),
          aTr = ($(this).hasClass('previous-row')) ? cTr.prev('tr') : cTr.next('tr');
      if (aTr.length!==0)
      {
          $(aTr).find('.btn.preview').trigger('click');
          $('.previous-row').toggleClass('disabled', (!aTr.prev('tr').length)?true:false);
          $('.next-row').toggleClass('disabled', (!aTr.next('tr').length)?true:false);
      }
  });

  // toggle preview display of row level data preview
  $('body').on( 'click', 'a[data-toggle="proto"][data-target="#preview"]', function (e){
      e.preventDefault();
      e.stopPropagation();

      var cTr = $($(this).parents('tr')[0]),
          pTr = $($(this).parents('tr').prev('tr')),
          nTr = $($(this).parents('tr').next('tr')),
          inPreview = cTr.hasClass('highlighted current'),
          oTable = $('#example');

      $('#example,tr').removeClass('highlighted above current below');

      // close preview pane
      if ( inPreview || $(this).attr('href')==='#' ) {
          $('body').removeClass('preview');
          return;
      }
      // handle condition when first row selected
      if (pTr.length===0) {
          $('.previous-row').toggleClass('disabled', true);
          pTr = $(this).parents('table').find('thead tr');
      }
      // or last row
      if (nTr.length===0) {
          $('.next-row').toggleClass('disabled', true);
          nTr = $(this).parents('table').find('tfoot tr');
      }

      $('body').addClass('preview');
      pTr.addClass('highlighted above');
      cTr.addClass('highlighted current');
      nTr.addClass('highlighted below');
  } );

  $('body').addClass('list');

  $('body').on('click','.actions > .delete, .actions .dropdown-menu .edit',function(){
    $('.dataTable tr').removeClass('active');
    $(this).closest('tr').addClass('active');

    // init edit mode
    if($(this).hasClass('edit') ){
      $('#example').addClass('inline-edit-active');
      var editid = Math.floor(Math.random()*999999);
      $('<tr id="edit-id-'+editid+'" class="tr-inline-edit single"></tr>').insertAfter($(this).closest('tr'));
      loadPartials([
         {"file":"common/table-cell-edit","target":"#edit-id-"+editid,"method":"append"},
      ]);

      $('.chzn-select').chosen({ disable_search_threshold: 5 });

      var tr_val = new Array(),
          tr_i = 0;
      $(this).closest('tr').find('td').each(function(el){
          //console.log($(this).text());
          tr_val[tr_i]=$(this).text();
          tr_i = tr_i + 1;
      });

      var tr_i = 0;
      $('#edit-id-'+editid).find('td').each(function(el){
        $(this).find('input[type=text]').attr('value',tr_val[tr_i]);
        tr_i = tr_i + 1;
      });

      $(this).closest('tr').addClass('hide');
    }
  });

  $('body').on('change', '.tr-inline-edit', function(){
    $('.btn.inline-save').removeClass('disabled');
  });

  $('body').on('click', '.tr-inline-edit .inline-cancel, .tr-inline-edit .inline-save', function(){
    $(this).closest('tr').prev().removeClass('hide active');
    $(this).closest('tr').remove();
    if($(this).hasClass('inline-save')) {
      throwMessage('<strong>Success!</strong> Your edits have been saved.', 'success', true);
    }
    $('#example').removeClass('inline-edit-active');
    return false;
  });

  $('body').on('click','.actions .dropdown-menu .delete',function(){
    $(this).closest('tr').remove();
    $('#example,tr').removeClass('highlighted above current below');
    $('body').removeClass('preview');

    throwMessage('<strong>Success!</strong> You successfully deleted the ' + page.module +'.', 'success', true);
  });

  $('body').on('click', '.cancel', function(){
      $('#example').removeClass('inline-edit-active');
      loadPartials([
         {"file":"common/header","target":".headerpane.ellipsis","method":"replace"},
      ]);
  });

  $('body').on('click', '.edit-all-records', function(){

    $('#example').addClass('inline-edit-active');

    $('#example tr').each(function(){
      var editid = Math.floor(Math.random()*999999);
      $('<tr id="edit-id-'+editid+'" class="tr-inline-edit"></tr>').insertAfter($(this));
      loadPartials([
         {"file":"common/table-all-cells-edit","target":"#edit-id-"+editid,"method":"append"},
         {"file":"common/header-edit-list","target":".headerpane.ellipsis","method":"replace"},
      ]);

      var tr_val = new Array(),
          tr_i = 0;

      $(this).find('td').each(function(el){
          tr_val[tr_i]=$(this).text();
          tr_i = tr_i + 1;
      });

      var tr_i = 0;
      $('#edit-id-'+editid).find('td').each(function(el){
        $(this).find('input[type=text]').attr('value',tr_val[tr_i]);
        tr_i = tr_i + 1;
      });

      $('.chzn-select').chosen({ disable_search_threshold: 5 });
      $('.headerpane.ellipsis').find('.save').addClass('disabled');
      $(this).closest('tr').addClass('hide');
    });
    return false;
  });

  $('body').on('click', '.headerpane.ellipsis .inline-save', function(){
    $('#example').removeClass('inline-edit-active');
    $('tr.tr-inline-edit').remove();
    $('tr.hide').removeClass('hide')
    throwMessage('<strong>Success!</strong> Your edits have been saved.', 'success', true);
    loadPartials([
      {"file":"common/header","target":".headerpane.ellipsis","method":"replace"},
    ]);
    return false;
  });

});
