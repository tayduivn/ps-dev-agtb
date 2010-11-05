/**
 * Edit functions for EAPM
 */
SUGAR.forms.EapmAction = function(source, target) {
    this.source = source;
	this.target = target;
}

SUGAR.util.extend(SUGAR.forms.EapmAction, SUGAR.forms.AbstractAction, {
	exec: function() {
			var sfield = SUGAR.forms.AssignmentHandler.VARIABLE_MAP[this.source];
			if ( sfield == null || sfield.value == null )	return null;
			var transl = SUGAR.language.get('app_list_strings', 'LBL_API_TYPE_ENUM');
			var eapmvalue = SUGAR.eapm[sfield.value];
			if(eapmvalue == null || eapmvalue.authMethods == null) return null;
			keys = []
			values = []
			for(v in eapmvalue.authMethods) {
				keys.push(v);
				values.push(transl[v]);
			}
			kenum = 'enum("'+keys.join('","')+'")';
			venum = 'enum("'+values.join('","')+'")';
			var setevent = new SUGAR.forms.SetOptionsAction(this.target, kenum, venum);
			setevent.exec();


	}
});

SUGAR.forms.EapmOauthAction = function(app, type, fields) {
    this.app = app;
    this.type = type;
    this.fields = fields;
}

SUGAR.util.extend(SUGAR.forms.EapmOauthAction, SUGAR.forms.AbstractAction, {
	exec: function() {
			var typefield = SUGAR.forms.AssignmentHandler.VARIABLE_MAP[this.type];
			if ( typefield == null || typefield.value != 'oauth' )	return null;
			
			var appfield = SUGAR.forms.AssignmentHandler.VARIABLE_MAP[this.app];
			if ( appfield == null || appfield.value == null )	return null;
			var eapmvalue = SUGAR.eapm[appfield.value];
			if(eapmvalue == null) return null;
			
			for(i in this.fields) {
				var setevent = new SUGAR.forms.VisibilityAction(this.fields[i], eapmvalue.oauthFixed?'false':'true', 'EditView');
				setevent.exec();
			}
	}
});
