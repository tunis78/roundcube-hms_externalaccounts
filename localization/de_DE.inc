<?php

$labels = array();

$labels['externalaccounts'] = 'Externe Konten';
$labels['editexternalaccounts'] = 'Externe Konten bearbeiten';
$labels['editexternalaccount'] = 'Externes Konto bearbeiten';
$labels['editexternalaccountfromtitle'] = 'Einstellungsformular für externes Konto';

$labels['newexternalaccount'] = 'Neues externes Konto';
$labels['disabled'] = 'Deaktiviert';
$labels['downloadnewmail'] = 'Neue E-Mails abrufen';

$labels['externalaccountsettings'] = 'Externes Konto Einstellungen';
$labels['enabled'] = 'Aktiviert';
$labels['name'] = 'Name';
$labels['serverinformation'] = 'Serverinformationen';
$labels['type'] = 'Typ';
$labels['serveraddress'] = 'Serveradresse';
$labels['tcp/ipport'] = 'TCP/IP Port';
$labels['connectionsecurity'] = 'Verbindungssicherheit';
$labels['none'] = 'Keine';
$labels['starttlsrequired'] = 'STARTTLS (Erforderlich)';
$labels['ssl/tls'] = 'SSL/TLS';
//$labels['username'] = 'Benutzername';
//$labels['password'] = 'Passwort';
//$labels['settings'] = 'Einstellungen';
$labels['minutesbetweendownload'] = 'Minuten zwischen Abruf';
$labels['delivertorecipientsinmimeheaders'] = 'An Empfänger in MIME header zustellen';
$labels['retrievedatefromreceivedheader'] = 'Datum aus Received header beziehen';
$labels['antispam'] = 'Anti-Spam';
$labels['antivirus'] = 'Anti-Virus';
$labels['allowrouterecipients'] = 'Erlaube Route-Empfänger';
$labels['deletemessagesimmediately'] = 'Nachrichten sofort löschen';
$labels['donotdeletemessages'] = 'Nachrichten nicht löschen';
$labels['deletemessagesafter'] = 'Nachrichten löschen nach';
$labels['days'] = 'Tagen';


$messages = array();
$messages['externalaccountsuccessfullyupdated'] = 'Externes Konto erfolgreich aktualisiert.';
$messages['externalaccountnowdownloading'] = 'Externes Konto wird abgerufen';
$messages['externalaccountdeleteconfirm'] = 'Soll das externe Konto wirklich gelöscht werden?';

$messages['internalerror'] = 'Fehler bei der ausgewählten Aktion.';
//$messages['connecterror'] = $messages['internalerror'] . ' Connection error.';
$messages['loaderror'] = 'Externe Konten konnten nicht geladen werden.';
$messages['loadconnecterror'] = $messages['loaderror'] . ' Connection error.';
$messages['updateerror'] = 'Externes Konto konnte nicht aktualisiert werden.';
$messages['updateconnecterror'] = $messages['updateerror'] . ' Connection error.';
