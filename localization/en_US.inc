<?php

$labels = array();

$labels['externalaccounts'] = 'External Accounts';
$labels['editexternalaccounts'] = 'Edit external accounts';
$labels['editexternalaccount'] = 'Edit external account';
$labels['editexternalaccountfromtitle'] = 'Edit form for external account';

$labels['newexternalaccount'] = 'New external account';
$labels['disabled'] = 'Disabled';
$labels['downloadnewmail'] = 'Download new mail';

$labels['externalaccountsettings'] = 'External account settings';
$labels['enabled'] = 'Active';
$labels['name'] = 'Name';
$labels['serverinformation'] = 'Server information';
$labels['type'] = 'Type';
$labels['serveraddress'] = 'Server address';
$labels['tcp/ipport'] = 'TCP/IP port';
$labels['connectionsecurity'] = 'Connection security';
$labels['none'] = 'None';
$labels['starttlsrequired'] = 'STARTTLS (Required)';
$labels['ssl/tls'] = 'SSL/TLS';
//$labels['username'] = 'User name';
//$labels['password'] = 'Password';
//$labels['settings'] = 'Settings';
$labels['minutesbetweendownload'] = 'Minutes between download';
$labels['delivertorecipientsinmimeheaders'] = 'Deliver to recipients in MIME headers';
$labels['retrievedatefromreceivedheader'] = 'Retrieve date from Received header';
$labels['antispam'] = 'Anti-spam';
$labels['antivirus'] = 'Anti-virus';
$labels['allowrouterecipients'] = 'Allow route recipients';
$labels['deletemessagesimmediately'] = 'Delete messages immediately';
$labels['donotdeletemessages'] = 'Do not delete messages';
$labels['deletemessagesafter'] = 'Delete messages after';
$labels['days'] = 'days';


$messages = array();
$messages['externalaccountsuccessfullyupdated'] = 'External account successfully updated';
$messages['externalaccountnowdownloading'] = 'External account now downloading';
$messages['externalaccountdeleteconfirm'] = 'Do you really want to delete selected external account?';

$messages['internalerror'] = 'Error with selected action.';
//$messages['connecterror'] = $messages['internalerror'] . ' Connection error.';
$messages['loaderror'] = 'Could not load external account(s).';
$messages['loadconnecterror'] = $messages['loaderror'] . ' Connection error.';
$messages['updateerror'] = 'Could not update external account.';
$messages['updateconnecterror'] = $messages['updateerror'] . ' Connection error.';