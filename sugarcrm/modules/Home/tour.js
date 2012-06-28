
var modals=new Array();
modals[0] = {
	target: "#moduleTab_AllHome", 
	title: "Home Icon", 
	content: "Quickly get back to your Home Page dashboard in one click.", 
	placement: "bottom left",
    leftOffset: 40,
    topOffset: -10
};
modals[1] = {
	target: "#moduleTab_AllAccounts", 
	title: "Modules", 
	content: "All your important modules are here.",  
	placement: "bottom"
};
modals[2] = {
	target: "#moduleTabExtraMenuAll", 
	title: "More Modules", 
	content: "The rest of your modules are here.",  
	placement: "bottom"
};
modals[3] = {
	target: "#dcmenuSearchDiv", 
	title: "Full Text Search", 
	content: "Search just got a whole lot better.", 
	placement: "bottom"
};
modals[4] = {
	target: "#dcmenuSugarCube", 
	title: "Notifications", 
	content: "SugarCRM application notifications.",  
	placement: "bottom"
};
modals[5] = {
	target: "#globalLinksModule", 
	title: "Profile", 
	content: "Access profile, settings and logout.", 
	placement: "bottom"
};
modals[6] = {
	target: "#quickCreate",
	title: "Quick Create", 
	content: "All previous quick create icons are now in one dropdown.",  
	placement: "bottom right",
    leftOffset: 40,
    topOffset: -10
};
modals[7] = {
	target: "#arrow",
	title: "Collapsible Footer", 
	content: "Easily expand and collapse the footer.", 
	placement: "top right",
    leftOffset: 80,
    topOffset: -40
};
modals[8] = {
	target: "#integrations", 
	title: "Custom Apps", 
	content: "Your custom integrations go here.", 
	placement: "top",
    leftOffset: -30
};
modals[9] = {
	target: "#logo", 
	title: "Your Brand", 
	content: "Your logo goes here. You can mouse over for more info.",  
	placement: "top"
};



$(document).ready(function() {
	SUGAR.tour.init({
		id: 'tour',
		modals: modals,
		modalUrl: "index.php?module=Home&action=tour&to_pdf=1",
		prefUrl: "index.php?module=Users&action=UpdateTourStatus&to_pdf=true&viewed=true",
        'class': 'whatsnew',
		onTourFinish: function() {
				$('#bootstrapJs').remove();
				$('#popoverext').remove();
				$('#bounce').remove();
				$('#bootstrapCss').remove();
				$('#tourCss').remove();
				$('#tourJs').remove();
				$('#whatsNewsJs').remove();
			}
		});	
});
	