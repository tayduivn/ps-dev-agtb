var tinyMCEPreInit = { settings : { themes : "advanced", plugins : "safari,inlinepopups,autosave,spellchecker,paste,wordpress,media,fullscreen", languages : "en", debug : false }, base : "https://sugarinternal.sugarondemand.com/salesportal/wp-includes/js/tinymce", suffix : "" };tinyMCEPreInit.start();tinyMCE.addI18n({en:{
common:{
edit_confirm:"Do you want to use the WYSIWYG mode for this textarea?",
apply:"Apply",
insert:"Insert",
update:"Update",
cancel:"Cancel",
close:"Close",
browse:"Browse",
class_name:"Class",
not_set:"-- Not set --",
clipboard_msg:"Copy/Cut/Paste is not available in Mozilla and Firefox.",
clipboard_no_support:"Currently not supported by your browser, use keyboard shortcuts instead.",
popup_blocked:"Sorry, but we have noticed that your popup-blocker has disabled a window that provides application functionality. You will need to disable popup blocking on this site in order to fully utilize this tool.",
invalid_data:"Error: Invalid values entered, these are marked in red.",
more_colors:"More colors"
},
contextmenu:{
align:"Alignment",
left:"Left",
center:"Center",
right:"Right",
full:"Full"
},
insertdatetime:{
date_fmt:"%Y-%m-%d",
time_fmt:"%H:%M:%S",
insertdate_desc:"Insert date",
inserttime_desc:"Insert time",
months_long:"January,February,March,April,May,June,July,August,September,October,November,December",
months_short:"Jan_January_abbreviation,Feb_February_abbreviation,Mar_March_abbreviation,Apr_April_abbreviation,May_May_abbreviation,Jun_June_abbreviation,Jul_July_abbreviation,Aug_August_abbreviation,Sep_September_abbreviation,Oct_October_abbreviation,Nov_November_abbreviation,Dec_December_abbreviation",
day_long:"Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday",
day_short:"Sun,Mon,Tue,Wed,Thu,Fri,Sat"
},
print:{
print_desc:"Print"
},
preview:{
preview_desc:"Preview"
},
directionality:{
ltr_desc:"Direction left to right",
rtl_desc:"Direction right to left"
},
layer:{
insertlayer_desc:"Insert new layer",
forward_desc:"Move forward",
backward_desc:"Move backward",
absolute_desc:"Toggle absolute positioning",
content:"New layer..."
},
save:{
save_desc:"Save",
cancel_desc:"Cancel all changes"
},
nonbreaking:{
nonbreaking_desc:"Insert non-breaking space character"
},
iespell:{
iespell_desc:"Run spell checking",
download:"ieSpell not detected. Do you want to install it now?"
},
advhr:{
advhr_desc:"Horizontale rule"
},
emotions:{
emotions_desc:"Emotions"
},
searchreplace:{
search_desc:"Find",
replace_desc:"Find/Replace"
},
advimage:{
image_desc:"Insert/edit image"
},
advlink:{
link_desc:"Insert/edit link"
},
xhtmlxtras:{
cite_desc:"Citation",
abbr_desc:"Abbreviation",
acronym_desc:"Acronym",
del_desc:"Deletion",
ins_desc:"Insertion",
attribs_desc:"Insert/Edit Attributes"
},
style:{
desc:"Edit CSS Style"
},
paste:{
paste_text_desc:"Paste as Plain Text",
paste_word_desc:"Paste from Word",
selectall_desc:"Select All"
},
paste_dlg:{
text_title:"Use CTRL+V on your keyboard to paste the text into the window.",
text_linebreaks:"Keep linebreaks",
word_title:"Use CTRL+V on your keyboard to paste the text into the window."
},
table:{
desc:"Inserts a new table",
row_before_desc:"Insert row before",
row_after_desc:"Insert row after",
delete_row_desc:"Delete row",
col_before_desc:"Insert column before",
col_after_desc:"Insert column after",
delete_col_desc:"Remove column",
split_cells_desc:"Split merged table cells",
merge_cells_desc:"Merge table cells",
row_desc:"Table row properties",
cell_desc:"Table cell properties",
props_desc:"Table properties",
paste_row_before_desc:"Paste table row before",
paste_row_after_desc:"Paste table row after",
cut_row_desc:"Cut table row",
copy_row_desc:"Copy table row",
del:"Delete table",
row:"Row",
col:"Column",
cell:"Cell"
},
autosave:{
unload_msg:"The changes you made will be lost if you navigate away from this page."
},
fullscreen:{
desc:"Toggle fullscreen mode (Alt+Shift+G)"
},
media:{
desc:"Insert / edit embedded media",
delta_width:"0",
delta_height:"0",
edit:"Edit embedded media"
},
fullpage:{
desc:"Document properties"
},
template:{
desc:"Insert predefined template content"
},
visualchars:{
desc:"Visual control characters on/off."
},
spellchecker:{
desc:"Toggle spellchecker (Alt+Shift+N)",
menu:"Spellchecker settings",
ignore_word:"Ignore word",
ignore_words:"Ignore all",
langs:"Languages",
wait:"Please wait...",
sug:"Suggestions",
no_sug:"No suggestions",
no_mpell:"No misspellings found."
},
pagebreak:{
desc:"Insert page break."
}}});

tinyMCE.addI18n("en.advanced",{
style_select:"Styles",
font_size:"Font size",
fontdefault:"Font family",
block:"Format",
paragraph:"Paragraph",
div:"Div",
address:"Address",
pre:"Preformatted",
h1:"Heading 1",
h2:"Heading 2",
h3:"Heading 3",
h4:"Heading 4",
h5:"Heading 5",
h6:"Heading 6",
blockquote:"Blockquote",
code:"Code",
samp:"Code sample",
dt:"Definition term ",
dd:"Definition description",
bold_desc:"Bold (Ctrl / Alt+Shift + B)",
italic_desc:"Italic (Ctrl / Alt+Shift + I)",
underline_desc:"Underline",
striketrough_desc:"Strikethrough (Alt+Shift+D)",
justifyleft_desc:"Align left (Alt+Shift+L)",
justifycenter_desc:"Align center (Alt+Shift+C)",
justifyright_desc:"Align right (Alt+Shift+R)",
justifyfull_desc:"Align full (Alt+Shift+J)",
bullist_desc:"Unordered list (Alt+Shift+U)",
numlist_desc:"Ordered list (Alt+Shift+O)",
outdent_desc:"Outdent",
indent_desc:"Indent",
undo_desc:"Undo (Ctrl+Z)",
redo_desc:"Redo (Ctrl+Y)",
link_desc:"Insert/edit link (Alt+Shift+A)",
link_delta_width:"0",
link_delta_height:"0",
unlink_desc:"Unlink (Alt+Shift+S)",
image_desc:"Insert/edit image (Alt+Shift+M)",
image_delta_width:"0",
image_delta_height:"0",
cleanup_desc:"Cleanup messy code",
code_desc:"Edit HTML Source",
sub_desc:"Subscript",
sup_desc:"Superscript",
hr_desc:"Insert horizontal ruler",
removeformat_desc:"Remove formatting",
forecolor_desc:"Select text color",
backcolor_desc:"Select background color",
charmap_desc:"Insert custom character",
visualaid_desc:"Toggle guidelines/invisible elements",
anchor_desc:"Insert/edit anchor",
cut_desc:"Cut",
copy_desc:"Copy",
paste_desc:"Paste",
image_props_desc:"Image properties",
newdocument_desc:"New document",
help_desc:"Help",
blockquote_desc:"Blockquote (Alt+Shift+Q)",
clipboard_msg:"Copy/Cut/Paste is not available in Mozilla and Firefox.",
path:"Path",
newdocument:"Are you sure you want to clear all contents?",
toolbar_focus:"Jump to tool buttons - Alt+Q, Jump to editor - Alt-Z, Jump to element path - Alt-X",
more_colors:"More colors",
colorpicker_delta_width:"0",
colorpicker_delta_height:"0"
});

tinyMCE.addI18n("en.advanced_dlg",{
about_title:"About TinyMCE",
about_general:"About",
about_help:"Help",
about_license:"License",
about_plugins:"Plugins",
about_plugin:"Plugin",
about_author:"Author",
about_version:"Version",
about_loaded:"Loaded plugins",
anchor_title:"Insert/edit anchor",
anchor_name:"Anchor name",
code_title:"HTML Source Editor",
code_wordwrap:"Word wrap",
colorpicker_title:"Select a color",
colorpicker_picker_tab:"Picker",
colorpicker_picker_title:"Color picker",
colorpicker_palette_tab:"Palette",
colorpicker_palette_title:"Palette colors",
colorpicker_named_tab:"Named",
colorpicker_named_title:"Named colors",
colorpicker_color:"Color:",
colorpicker_name:"Name:",
charmap_title:"Select custom character",
image_title:"Insert/edit image",
image_src:"Image URL",
image_alt:"Image description",
image_list:"Image list",
image_border:"Border",
image_dimensions:"Dimensions",
image_vspace:"Vertical space",
image_hspace:"Horizontal space",
image_align:"Alignment",
image_align_baseline:"Baseline",
image_align_top:"Top",
image_align_middle:"Middle",
image_align_bottom:"Bottom",
image_align_texttop:"Text top",
image_align_textbottom:"Text bottom",
image_align_left:"Left",
image_align_right:"Right",
link_title:"Insert/edit link",
link_url:"Link URL",
link_target:"Target",
link_target_same:"Open link in the same window",
link_target_blank:"Open link in a new window",
link_titlefield:"Title",
link_is_email:"The URL you entered seems to be an email address, do you want to add the required mailto: prefix?",
link_is_external:"The URL you entered seems to external link, do you want to add the required http:// prefix?",
link_list:"Link list"
});

tinyMCE.addI18n("en.media_dlg",{
title:"Insert / edit embedded media",
general:"General",
advanced:"Advanced",
file:"File/URL",
list:"List",
size:"Dimensions",
preview:"Preview",
constrain_proportions:"Constrain proportions",
type:"Type",
id:"Id",
name:"Name",
class_name:"Class",
vspace:"V-Space",
hspace:"H-Space",
play:"Auto play",
loop:"Loop",
menu:"Show menu",
quality:"Quality",
scale:"Scale",
align:"Align",
salign:"SAlign",
wmode:"WMode",
bgcolor:"Background",
base:"Base",
flashvars:"Flashvars",
liveconnect:"SWLiveConnect",
autohref:"AutoHREF",
cache:"Cache",
hidden:"Hidden",
controller:"Controller",
kioskmode:"Kiosk mode",
playeveryframe:"Play every frame",
targetcache:"Target cache",
correction:"No correction",
enablejavascript:"Enable JavaScript",
starttime:"Start time",
endtime:"End time",
href:"Href",
qtsrcchokespeed:"Choke speed",
target:"Target",
volume:"Volume",
autostart:"Auto start",
enabled:"Enabled",
fullscreen:"Fullscreen",
invokeurls:"Invoke URLs",
mute:"Mute",
stretchtofit:"Stretch to fit",
windowlessvideo:"Windowless video",
balance:"Balance",
baseurl:"Base URL",
captioningid:"Captioning id",
currentmarker:"Current marker",
currentposition:"Current position",
defaultframe:"Default frame",
playcount:"Play count",
rate:"Rate",
uimode:"UI Mode",
flash_options:"Flash options",
qt_options:"Quicktime options",
wmp_options:"Windows media player options",
rmp_options:"Real media player options",
shockwave_options:"Shockwave options",
autogotourl:"Auto goto URL",
center:"Center",
imagestatus:"Image status",
maintainaspect:"Maintain aspect",
nojava:"No java",
prefetch:"Prefetch",
shuffle:"Shuffle",
console:"Console",
numloop:"Num loops",
controls:"Controls",
scriptcallbacks:"Script callbacks",
swstretchstyle:"Stretch style",
swstretchhalign:"Stretch H-Align",
swstretchvalign:"Stretch V-Align",
sound:"Sound",
progress:"Progress",
qtsrc:"QT Src",
qt_stream_warn:"Streamed rtsp resources should be added to the QT Src field under the advanced tab.",
align_top:"Top",
align_right:"Right",
align_bottom:"Bottom",
align_left:"Left",
align_center:"Center",
align_top_left:"Top left",
align_top_right:"Top right",
align_bottom_left:"Bottom left",
align_bottom_right:"Bottom right",
flv_options:"Flash video options",
flv_scalemode:"Scale mode",
flv_buffer:"Buffer",
flv_startimage:"Start image",
flv_starttime:"Start time",
flv_defaultvolume:"Default volume",
flv_hiddengui:"Hidden GUI",
flv_autostart:"Auto start",
flv_loop:"Loop",
flv_showscalemodes:"Show scale modes",
flv_smoothvideo:"Smooth video",
flv_jscallback:"JS Callback"
});

tinyMCE.addI18n("en.wordpress",{
wp_adv_desc:"Show/Hide Kitchen Sink (Alt+Shift+Z)",
wp_more_desc:"Insert More tag (Alt+Shift+T)",
wp_page_desc:"Insert Page break (Alt+Shift+P)",
wp_help_desc:"Help (Alt+Shift+H)",
wp_more_alt:"More...",
wp_page_alt:"Next page..."
});

tinyMCE.init({mode:"none",onpageload:"wpEditorInit",width:"100%",theme:"advanced",skin:"wp_theme",theme_advanced_buttons1:"bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,image,wp_more,|,spellchecker,fullscreen,wp_adv",theme_advanced_buttons2:"formatselect,underline,justifyfull,forecolor,|,pastetext,pasteword,removeformat,|,media,charmap,|,outdent,indent,|,undo,redo,wp_help",theme_advanced_buttons3:"",theme_advanced_buttons4:"",language:"en",spellchecker_languages:"+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_statusbar_location:"bottom",theme_advanced_resizing:"1",theme_advanced_resize_horizontal:"",dialog_type:"modal",relative_urls:"",remove_script_host:"",convert_urls:"",apply_source_formatting:"",remove_linebreaks:"1",paste_convert_middot_lists:"1",paste_remove_spans:"1",paste_remove_styles:"1",gecko_spellcheck:"1",entities:"38,amp,60,lt,62,gt",accessibility_focus:"",tab_focus:":next",content_css:"https://sugarinternal.sugarondemand.com/salesportal/wp-includes/js/tinymce/wordpress.css",save_callback:"switchEditors.saveCallback",plugins:"safari,inlinepopups,autosave,spellchecker,paste,wordpress,media,fullscreen"});