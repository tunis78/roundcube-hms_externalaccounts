<?php

$labels = array();

$labels['externalaccounts'] = 'Külső fiókok';
$labels['editexternalaccounts'] = 'Külső fiókok szerkeztése';
$labels['editexternalaccount'] = 'Külső fiók szerkeztése';
$labels['editexternalaccountfromtitle'] = 'Külső fiók űrlapjának szerkesztése';

$labels['newexternalaccount'] = 'Új külső fiók';
$labels['disabled'] = 'Tiltva';
$labels['downloadnewmail'] = 'Töltse le az új leveleket';

$labels['externalaccountsettings'] = 'Külső fiók beállításai';
$labels['enabled'] = 'Aktív?';
$labels['name'] = 'Név';
$labels['serverinformation'] = 'Szerver információ';
$labels['type'] = 'Típus';
$labels['serveraddress'] = 'Szerver címe';
$labels['tcp/ipport'] = 'TCP/IP port';
$labels['connectionsecurity'] = 'Kapcsolat biztonsága';
$labels['none'] = 'Egyik sem';
$labels['starttlsrequired'] = 'STARTTLS (Required)';
$labels['ssl/tls'] = 'SSL/TLS';
//$labels['username'] = 'User name';
//$labels['password'] = 'Password';
//$labels['settings'] = 'Settings';
$labels['minutesbetweendownload'] = 'Percek a letöltés között';
$labels['delivertorecipientsinmimeheaders'] = 'Kézbesítés a címzetteknek MIME fejlécekben';
$labels['retrievedatefromreceivedheader'] = 'Dátum lekérése a Fogadott fejlécből';
$labels['antispam'] = 'Levélszemét szűrő';
$labels['antivirus'] = 'Vírusirtó';
$labels['allowrouterecipients'] = 'Engedélyezze az útvonal címzettjeit';
$labels['deletemessagesimmediately'] = 'Azonnal törölje az üzeneteket';
$labels['donotdeletemessages'] = 'Ne törölje az üzeneteket';
$labels['deletemessagesafter'] = 'Üzenetek törlése eltelt nap után';
$labels['days'] = 'nap';


$messages = array();
$messages['externalaccountsuccessfullyupdated'] = 'Külső fiók sikeresen frissítve';
$messages['externalaccountnowdownloading'] = 'Külső fiók letöltése folyamatban';
$messages['externalaccountdeleteconfirm'] = 'Biztosan törli a kijelölt külső fiókot?';

$messages['internalerror'] = 'Hiba a kiválasztott művelettel.';
//$messages['connecterror'] = $messages['internalerror'] . ' Connection error.';
$messages['loaderror'] = 'A külső fiók (ok) betöltése nem sikerült.';
$messages['loadconnecterror'] = $messages['loaderror'] . ' Connection error.';
$messages['updateerror'] = 'Nem sikerült frissíteni a külső fiókot.';
$messages['updateconnecterror'] = $messages['updateerror'] . ' Connection error.';
