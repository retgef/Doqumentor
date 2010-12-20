/**
	This file is part of Doqumentor.

    Doqumentor is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Doqumentor is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Doqumentor.  If not, see <http://www.gnu.org/licenses/>.
*/
$(function() {
mydoc = $('#doqument');
elements = mydoc.find('.constant, .function, .class, .method');
elements.find('H2').addClass('ui-widget-header').addClass('ui-corner-all').css('padding-left', ' 0.5em').css('cursor', 'pointer');
elements.children(':not(H2)').hide();
$('.methods').hide();
viewableElements = elements.find('H2:visible');
elements.children('H2').toggle(function(event) {
	event.preventDefault();
	show($(this).parent());
}, function(event) {
	event.preventDefault();
	hide($(this).parent());
});

dialogWidth = $(window).width()*0.9;
dialogHeight = $(window).height()*0.9;

mydoc.dialog({
		autoOpen: false,
		modal: true,
		position: 'center',
		width: dialogWidth,
		height: dialogHeight,
		title: 'Doqumentor'	
});
});

function show(elm) {
	elm.children().show();
	if(elm.hasClass('class')) elm.parent().children('.methods').show();
}
function hide(elm) {
	elm.children(':not(H2:first)').hide();
	if(elm.hasClass('class')) elm.parent().children('.methods').hide();
}
function search(query) {
	query = query.toLowerCase();
	results = viewableElements.parent('DIV[title^="' + query + '"]');
	viewableElements.hide();
	results.children('H2').show();
}