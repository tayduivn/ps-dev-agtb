function scoreEnableInlineEditor() {
  // Find the list view table
  var i = 0;
  var tmp = document.getElementsByTagName('table');

  for ( i = 0 ; i < tmp.length; i++ ) {
      if ( tmp[i] != null && tmp[i].className != null && ( tmp[i].className == 'listView' || tmp[i].className == 'listview' ) ) {
	  	  var dataTable = tmp[i];
	  	  break;
      }
  }

  if ( dataTable == null ) {
      // All hope is lost.
      console.log('All hope is lost');
      return;
  }

  var rowList = dataTable.getElementsByTagName('tr');
  var colList;
  var record;
  var module = 'Campaigns';
  for ( i = 0 ; i < rowList.length ; i++ ) {
	colList = rowList[i].getElementsByTagName('td');
	// This, sadly, is the only semi-solid way to trim out the header/footer rows
	if ( colList[0] == null || ( colList[0].className != 'evenListRowS1' && colList[0].className != 'oddListRowS1' ) ) {
	  continue;
	}

	record = colList[0].getElementsByTagName('a')[0].href.replace(/^.*record=/,"").replace(/&.*$/,'');

	scoreEnableEditorForField(colList[campaignScoreCol],record,module,'score');
	scoreEnableEditorForField(colList[campaignMulCol],record,module,'mul');
	
  }
}

function scoreEnableEditorForField( elem, record, module, field ) {
  // Make sure all table cells show up
  elem.innerHTML = elem.innerHTML + '&nbsp;';

  elem.style.border="1px solid grey";

  elem.sugar_record = record;
  elem.sugar_module = module;
  elem.sugar_field = field;

  var value = '';
  if ( elem.childNodes[0].tagName == 'SPAN' ) {
      value = elem.childNodes[0].innerHTML;
  } else {
      value = elem.innerHTML;
  }
  value = parseFloat(value);

  if ( isNaN(value) ) { value = 0; }
  if ( field == 'mul' ) {
      elem.innerHTML = value+'%';
  } else {
      elem.innerHTML = value;
  }

  elem.setAttribute('onclick','scorePopupEditor(this)');
}

function scorePopupEditor( elem ) {
  scoreSavePopupEditor();
  //console.log('popping up editor');
  var editElem = document.createElement('input');
  editElem.type = 'text';
  editElem.id = 'inlineEditElem';
  editElem.setAttribute('onblur','scoreSavePopupEditor()');

  var value = elem.innerHTML;
  editElem.value = value;
  editElem.plainElem = elem;
  elem.innerHTML = '';
  editElem.style['z-index'] = '-9999';
  elem.scrollIntoView();
  /*
  editElem.style.position = 'absolute';
  var tmp = elem.getClientRects()[0];
  editElem.style.top = tmp.top;
  editElem.style.left = tmp.left;
  editElem.style.width = tmp.right - tmp.left;
  editElem.style.height = tmp.bottom - tmp.top;
  */
  editElem.style.display = 'block';
  elem.appendChild(editElem);
  editElem.focus();
}

function scoreSavePopupEditor() {
  var elem = document.getElementById('inlineEditElem');
  if ( elem != null && elem.plainElem != null ) {
	//console.log('Saving: '+elem.value);
	//	call_json_method(elem.plainElem.sugar_module,'Save','record='+elem.plainElem.sugar_record+'&'+elem.plainElem.sugar_field+'='+elem.value,'inlineEdit',scoreSaveComplete);
	var argStr = 'module='+elem.plainElem.sugar_module+'&action=Save&method=Save&record='+elem.plainElem.sugar_record+'&'+elem.plainElem.sugar_field+'='+elem.value;
	global_xmlhttp.open('POST','index.php?'+argStr,true);
	global_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	global_xmlhttp.send(argStr);

	elem.plainElem.removeChild(elem);
	elem.plainElem.innerHTML = elem.value;
	elem.plainElem = null;
	elem.style.display='none';
  }
}

scoreEnableInlineEditor();
