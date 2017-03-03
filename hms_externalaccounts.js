/**
 * hms_externalaccounts plugin script
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (c) 2017, Andreas Tunberg <andreas@tunberg.com>
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 */

window.rcmail && rcmail.addEventListener('init', function(evt) {

    //Add class to disabled external accounts
    if (rcmail.env.disabled_externalaccounts) {
        var label_disabled = rcmail.get_label('hms_externalaccounts.disabled');
        $.each(rcmail.env.disabled_externalaccounts, function(k, v) {$('#rcmrow' + v).addClass('disabled').children('td').append(' - ' + label_disabled);});
    }

    if (rcmail.env.externalaccounts_reload) {
        window.top.location.href = rcmail.env.comm_path + '&_action=plugin.externalaccounts&_eaid=' + rcmail.env.externalaccounts_reload
    }

    rcmail.register_command('plugin.hmsexternalaccounts-add', function() { rcmail.hmsexternalaccounts_add() }, true);
    rcmail.register_command('plugin.hmsexternalaccounts-delete', function() { rcmail.hmsexternalaccounts_del() });
    rcmail.register_command('plugin.hmsexternalaccounts-download', function() { rcmail.hmsexternalaccounts_download() });

    if (rcmail.gui_objects.externalaccountlist) {
        rcmail.externalaccounts_list = new rcube_list_widget(rcmail.gui_objects.externalaccountlist,
          {multiselect:false, draggable:false, keyboard:true});

        rcmail.externalaccounts_list
          .addEventListener('select', function(e) { rcmail.hmsexternalaccounts_select(e); })
          .init();

        if (rcmail.env.externalaccounts_selected) {
            rcmail.externalaccounts_list.select(rcmail.env.externalaccounts_selected);
        }
    }

    if (rcmail.gui_objects.externalaccountform) {  
        rcmail.register_command('plugin.hmsexternalaccount-submit', function() {
            rcmail.set_busy(true, 'loading');
            rcmail.gui_objects.externalaccountform.submit();
        }, true);
    }

    $('input:not(:hidden):first').focus();

});

// External accounts selection
rcube_webmail.prototype.hmsexternalaccounts_select = function(list)
{
    var id = list.get_single_selection();

    if (id != null) {
        this.load_hmsexternalaccountframe(id);
        this.enable_command('plugin.hmsexternalaccounts-delete','plugin.hmsexternalaccounts-download', true);
    }
};

// button actions
rcube_webmail.prototype.hmsexternalaccounts_add = function()
{
    this.load_hmsexternalaccountframe();
    this.externalaccounts_list.clear_selection();
    this.enable_command('plugin.hmsexternalaccounts-delete','plugin.hmsexternalaccounts-download', false);
};

rcube_webmail.prototype.hmsexternalaccounts_del = function()
{
    var id = this.externalaccounts_list.get_single_selection();
    if (id != null && confirm(this.get_label('hms_externalaccounts.externalaccountdeleteconfirm'))) {
        this.set_busy(true);
        this.addEventListener('plugin.externalaccounts-reload', function() { location.href = './?_task=settings&_action=plugin.externalaccounts' } );
        this.http_post('plugin.externalaccounts-actions', '_act=delete&_eaid=' + id);
        this.set_busy(false);
    }
};

rcube_webmail.prototype.hmsexternalaccounts_download = function()
{
    var id = this.externalaccounts_list.get_single_selection();
    if (id != null) {
        this.http_post('plugin.externalaccounts-actions', '_act=download&_eaid=' + id);
    }
};


// load externalaccount frame
rcube_webmail.prototype.load_hmsexternalaccountframe = function(id)
{
    var has_id = typeof(id) != 'undefined' && id != null;
    if (this.env.contentframe && window.frames && window.frames[this.env.contentframe]) {
        target = window.frames[this.env.contentframe];
        var lock = '';//this.set_busy(true, 'loading');
        target.location.href = this.env.comm_path + '&_action=plugin.externalaccounts-edit'
          + (has_id ? '&_eaid='+id : '') + '&_unlock=' + lock;
    }
};