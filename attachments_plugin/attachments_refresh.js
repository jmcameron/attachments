/**
 * Define Javascript function for refreshing attachments lists
 * 
 * Copyright (C) 2010-2012 Jonathan M. Cameron, All Rights Reserved
 * License: http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
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
    if ( !alist ) {
        id = "attachmentsList_" + ptype + "_default_" + pid;
        alist = window.parent.document.getElementById(id);
        }
    var a = new Request({ 
        url: url,
        method: 'get', 
        onComplete: function( response ) {  
            
            // Refresh the attachments list
            alist.innerHTML = response;
            
	    // Remove any old click events (since they are for a deleted/updated SqueezeBox)
            $$('a.modal-button').removeEvents('click');

            // Since the html has been replaced, we need to reconnect the modal button events
            SqueezeBox.initialize({});
            SqueezeBox.assign($$('a.modal-button'), {
                parse: 'rel'
            });

        }}).send();
};
