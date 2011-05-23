function ibmO_roadmapChange() {
    var roadmapPicker = document.getElementById('roadmap_picker').value;
    document.getElementById('outerTab3').innerHTML = '<div style="text-align: center; width: 100%"><img src="themes/default/images/loading.gif"></div>';
    YAHOO.util.Connect.asyncRequest('GET','index.php?sugar_body_only=1&module=Opportunities&action=RoadmapViewer&record='+document.forms.DetailView.record.value+'&roadmapId='+roadmapPicker,{success:ibmO_roadmapReturn});
}

function ibmO_roadmapReturn(o) {
    document.getElementById('outerTab3').innerHTML = o.responseText;
    SUGAR.util.evalScript(o.responseText);

    ibmO_forceEnableTryCount = 0;
    setTimeout(ibmO_forceEnableQS,500);
    
}

function ibmO_forceEnableQS() {
    enableQS(true);
    
    var foundSomething = false;
    for ( idx in QSProcessedFieldsArray ) {
        foundSomething = true;
    }
    if ( !foundSomething && ibmO_forceEnableTryCount++ < 10 ) {
        setTimeout(ibmO_forceEnableQS,500);
    } else {
        ibmO_forceEnableTryCount = 0;
    }
}


// This is for setting up default Opportunity fields
function ibmO_showSetDefaults(viewLabel) {

    DCMenu.loadView(viewLabel,'index.php?module=Opportunities&action=SetDefaults&to_pdf=1');
}

function ibmO_saveSetDefaults( formElem ) {
    YAHOO.util.Connect.setForm(formElem);
    YAHOO.util.Connect.asyncRequest('POST','index.php?module=Opportunities&action=SaveDefaults&to_pdf=1', {success:(function(){DCMenu.closeOverlay();})});

//    YAHOO.util.Connect.asyncRequest('POST','index.php?module=Opportunities&action=SaveDefaults&to_pdf=1', {success:(function(){ibmO_showSetDefaults("Reload");})});

    return false;
}