/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2021 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */
jQuery((function(e){function a(){e(".hu-megamenu-item-list").sortable({connectWith:".hu-megamenu-item-list",items:" .hu-megamenu-item",placeholder:"drop-highlight",start:function(e,a){a.placeholder.height(a.item.height())},stop:function(e,a){}}).disableSelection(),e(".hu-megamenu-module-list").sortable({connectWith:".hu-megamenu-item-list",items:" .hu-megamenu-draggable-module",placeholder:"drop-highlight",helper:"clone",start:function(e,a){a.placeholder.height(a.item.height())},update:function(t,n){var u=n.item.text(),o='<div class="hu-megamenu-item-module"><div class="hu-megamenu-item-module-title"><a href="javascript:;" class="hu-megamenu-remove-module"><span class="fas fa-times" aria-hidden="true"></span></a><span>'+u+"</span></div></div>";n.item.removeAttr("style class").addClass("hu-megamenu-item").html(o),n.item.clone().insertAfter(n.item.html('<span class="fas fa-arrows-alt" aria-hidden="true"></span> '+u).removeAttr("class").addClass("hu-megamenu-draggable-module")),e(this).sortable("cancel"),a()}}).disableSelection(),e(".hu-megamenu-row").sortable({start:function(e,a){a.placeholder.height(a.item.height()),a.placeholder.width(a.item.width()-50)},items:".hu-megmenu-col",handle:".hu-action-move-column",placeholder:"drop-col-highlight",stop:function(e,a){}}),e("#hu-megamenu-layout").sortable({start:function(e,a){a.placeholder.height(a.item.height()),a.placeholder.width(a.item.width()-50)},items:".hu-megamenu-row",handle:".hu-action-move-row",placeholder:"drop-highlight",stop:function(e,a){}}),e(document).on("click",".hu-megamenu-remove-module",(function(a){a.preventDefault(),e(this).closest(".hu-megamenu-item").remove()}))}e("#attrib-helixultimatemegamenu").find(".control-group").first().find(".control-label").remove(),e("#attrib-helixultimatemegamenu").find(".control-group").first().find(">.controls").removeClass().addClass("megamenu").unwrap(),e(document).on("click","#hu-megamenu-toggler",(function(a){var t=e(this).is(":checked");e("#hu-megamenu-layout").data("megamenu",t),t?(e(".hu-megamenu-field-control, .hu-megamenu-sidebar").removeClass("hide-menu-builder"),e(".hu-dropdown-field-control").addClass("hide-menu-builder")):(e(".hu-megamenu-field-control, .hu-megamenu-sidebar").addClass("hide-menu-builder"),e(".hu-dropdown-field-control").removeClass("hide-menu-builder"))})),e(document).on("change","#hu-megamenu-width",(function(a){e("#hu-megamenu-layout").data("width",e(this).val())})),e(document).on("change","#hu-megamenu-alignment",(function(a){e("#hu-megamenu-layout").data("menualign",e(this).val())})),e(document).on("click","#hu-megamenu-title-toggler",(function(a){e("#hu-megamenu-layout").data("showtitle",e(this).is(":checked"))})),e(document).on("change","#hu-megamenu-dropdown",(function(a){e("#hu-megamenu-layout").data("dropdown",e(this).val())})),e(document).on("change","#hu-megamenu-fa-icon",(function(a){e("#hu-megamenu-layout").data("faicon",e(this).val())})),e(document).on("change","#hu-megamenu-custom-class",(function(a){e("#hu-megamenu-layout").data("customclass",e(this).val())})),e(document).on("change","#hu-megamenu-menu-badge",(function(a){e("#hu-megamenu-layout").data("badge",e(this).val())})),e(document).on("change","#hu-megamenu-badge-position",(function(a){e("#hu-megamenu-layout").data("badge_position",e(this).val())})),e(document).on("change","#hu-menu-badge-bg-color",(function(a){e("#hu-megamenu-layout").data("badge_bg_color",e(this).val())})),e(document).on("change","#hu-menu-badge-text-color",(function(a){e("#hu-megamenu-layout").data("badge_text_color",e(this).val())})),document.adminForm.onsubmit=function(a){var t=[];e("#hu-megamenu-layout").find(".hu-megamenu-row").each((function(a){var n=e(this),u=a;t[u]={type:"row",attr:[]},n.find(".hu-megmenu-col").each((function(a){var n=e(this),o=a,i=n.attr("data-grid");t[u].attr[o]={type:"column",colGrid:i,menuParentId:"",moduleId:"",items:[]};var m="";n.find("h4").each((function(a,t){m+=e(this).data("current_child")+","})),m&&(m=m.slice(",",-1),t[u].attr[o].menuParentId=m);var l="";n.find(".hu-megamenu-item").each((function(a,n){l+=e(this).data("mod_id")+",";var i=e(this).data("type"),m=e(this).data("mod_id");t[u].attr[o].items[a]={type:i,item_id:m}})),l&&(l=l.slice(",",-1),t[u].attr[o].moduleId=l)}))}));var n=e("#hu-megamenu-layout").data(),u={width:n.width||"0",menuitem:n.menuitem,menualign:n.menualign,megamenu:n.megamenu,showtitle:n.showtitle,faicon:n.faicon,customclass:n.customclass,dropdown:n.dropdown,badge:n.badge,badge_position:n.badge_position,badge_bg_color:n.badge_bg_color,badge_text_color:n.badge_text_color,layout:t};e("#jform_params_helixultimatemenulayout").val(JSON.stringify(u))},e(document).on("click","#hu-choose-megamenu-layout",(function(a){a.preventDefault(),e("#hu-megamenu-layout-modal").toggle()})),e(document).on("click",".hu-megamenu-grids",(function(t){t.preventDefault();var n=e(this).attr("data-layout"),u='<div class="hu-megamenu-row">';u+='<div class="hu-megamenu-row-actions clearfix">',u+='<div class="hu-action-move-row">',u+='<span class="fas fa-sort" aria-hidden="true"></span> Row',u+="</div>",u+='<a href="#" class="hu-action-detele-row"><span class="far fa-trash-alt" aria-hidden="true"></span></a>',u+="</div>",u+='<div class="hu-row">';var o='<div class="hu-megmenu-col hu-col-sm-{col}" data-grid="{grid}">';o+='<div class="hu-megamenu-column">',o+='<div class="hu-megamenu-column-actions">',o+='<span class="hu-action-move-column"><span class="fas fa-arrows-alt" aria-hidden="true"></span> Column</span>',o+="</div>",o+='<div class="hu-megamenu-item-list"></div>',o+="</div>",o+="</div>";var m="";if(12!=n){var l=n.split("+");for(i=0;i<l.length;i++)m+=o.replace("{col}",l[i]).replace("{grid}",l[i])}else m+=o.replace("{col}",12).replace("{grid}",12);u+=m,u+="</div>",u+="</div>",e("#hu-megamenu-layout").append(u),e(this).closest("#hu-megamenu-layout-modal").hide(),a()})),a(),e(document).on("click",".hu-action-detele-row",(function(a){a.preventDefault(),e(this).closest(".hu-megamenu-row").remove()}))}));