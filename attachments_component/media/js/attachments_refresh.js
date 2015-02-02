/**
 * Define Javascript function for refreshing attachments lists
 * 
 * Copyright (C) 2010-2015 Jonathan M. Cameron, All Rights Reserved
 * License: http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 *
 * By Jonathan Cameron
 *
 * @param siteURL string the site base URL for the request
 * @param ptype string parent_type for the attachments list parent
 * @param pentity string parent_entity for the attachments list parent
 * @param pid integer parent_id for the attachments list parent
 * @param lang the current language ('' if not specified)
 * @param from string the 'from' clause to pass in the request
 */

function refreshAttachments(siteUrl, ptype, pentity, pid, lang, from) {
    var id = "attachmentsList_" + ptype + "_" + pentity + "_" + pid,
        alist = document.getElementById(id),
        url = siteUrl + "/index.php?option=com_attachments&task=attachmentsList";
    url += "&parent_id=" + pid;
    url += "&parent_type=" + ptype + "&parent_entity=" + pentity;
    url += "&lang=" + lang;
    url += "&from=" + from + "&tmpl=component&format=raw";
    if (!alist) {
        alist = window.parent.document.getElementById(id);
    }
    if (!alist) {
        id = "attachmentsList_" + ptype + "_default_" + pid;
        alist = window.parent.document.getElementById(id);
    }
    new window.Request({
        url: url,
        method: 'get',
        onComplete: function (response) {

            // Refresh the attachments list
            alist.innerHTML = response;

            // Remove any old click events (since they are for a deleted/updated SqueezeBox)
            $$('a.modal-button').removeEvents('click');

            // Since the html has been replaced, we need to reconnect the modal button events
            window.SqueezeBox.initialize({});
            window.SqueezeBox.assign($$('a.modal-button'), { parse: 'rel' });
        }
    }).send();
};


