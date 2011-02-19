/**
 * Define Javascript function for refreshing attachments lists
 * 
 * Copyright (C) 2010 Jonathan M. Cameron, All Rights Reserved
 * License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 * By Jonathan Cameron
 *
 * @param siteURL string the site base URL for the request
 * @param ptype string parent_type for the attachments list parent
 * @param pentity string parent_entity for the attachments list parent
 * @param pid integer parent_id for the attachments list parent
 * @param from string the 'from' clause to pass in the request
 */

function refreshAttachments(siteUrl, ptype, pentity, pid, from) {
    var url = siteUrl + "/index.php?option=com_attachments&task=attachmentsList";
    url += "&parent_id=" + pid;
    url += "&parent_type=" + ptype + "&parent_entity=" + pentity;
    url += "&from=" + from + "&tmpl=component&format=raw";
    id = "attachmentsList_" + ptype + "_" + pentity + "_" + pid;
    var alist = document.getElementById(id);
    if ( !alist ) {
	alist = window.parent.document.getElementById(id);
	}
    var a = new Ajax( url, { 
                method: 'get', 
		onComplete: function( response ) {  

		   // Refresh the attachments list
                   alist.innerHTML = response;

		   // Since the html has been replaced, we need to reconnect the events
		   // (Note: addEvent checks to prevent adding duplicate events)
		   $$('a.modal-button').each(function(el) {
			el.addEvent('click', function(e) {
			    new Event(e).stop();
			    SqueezeBox.fromElement(el);
			    });
			});

	    }}).request();
};
