<?php

/**
 * hMailserver remote external accounts changer
 *
 * @version 1.0
 * @author Andreas Tunberg <andreas@tunberg.com>
 *
 * Copyright (C) 2017, Andreas Tunberg
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */
 
$rc_remote_ip = 'YOUR ROUNDCUBE IP ADDRESS';

/*****************/

if($_SERVER['REMOTE_ADDR'] !== $rc_remote_ip)
{
	header('HTTP/1.0 403 Forbidden');
	exit('You are forbidden!');
}

define('HMS_ERROR', 1);
define('HMS_SUCCESS', 0);

if (empty($_POST['action']) || empty($_POST['email']) || empty($_POST['password']))
	sendResult('Required fields can not be empty.', HMS_ERROR);

$action = $_POST['action'];
$email = $_POST['email'];
$password = $_POST['password'];

try {
	$obApp = new COM("hMailServer.Application", NULL, CP_UTF8);
}
catch (Exception $e) {
	sendResult(trim(strip_tags($e->getMessage())), HMS_ERROR);
}
$temparr = explode('@', $email);
$domain = $temparr[1];
$obApp->Authenticate($email, $password);
try {
	$obAccount = $obApp->Domains->ItemByName($domain)->Accounts->ItemByAddress($email);

	switch($action){
		case 'externalaccounts_load':
			sendResult(externalaccountsLoad($obAccount->FetchAccounts()));
		case 'externalaccount_load':
			$externalaccount = $obAccount->FetchAccounts->ItemByDBID((int)$_POST['eaid']);
			$eadata=array();
			$eadata['enabled'] = $externalaccount->Enabled ?: 0;
			$eadata['name'] = $externalaccount->Name;
			$eadata['daystokeepmessages'] = $externalaccount->DaysToKeepMessages;
			$eadata['minutesbetweenfetch'] = $externalaccount->MinutesBetweenFetch;
			$eadata['port'] = $externalaccount->Port;
			$eadata['processmimerecipients'] = $externalaccount->ProcessMIMERecipients ?: 0;
			$eadata['processmimedate'] = $externalaccount->ProcessMIMEDate ?: 0;
			$eadata['serveraddress'] = $externalaccount->ServerAddress;
			$eadata['username'] = $externalaccount->Username;
			$eadata['useantispam'] = $externalaccount->UseAntiSpam ?: 0;
			$eadata['useantivirus'] = $externalaccount->UseAntiVirus ?: 0;
			$eadata['enablerouterecipients'] = $externalaccount->EnableRouteRecipients ?: 0;
			$eadata['connectionsecurity'] = $externalaccount->ConnectionSecurity;
			sendResult($eadata);
		case 'externalaccount_edit':
			if ($eaid = (int)$_POST['eaid'])
				$externalaccount = $obAccount->FetchAccounts->ItemByDBID($eaid);
			else
				$externalaccount = $obAccount->FetchAccounts->Add();

			$externalaccount->Enabled = $_POST['enabled'] == null ? 0 : 1;
			$externalaccount->Name = $_POST['name'];
			$externalaccount->DaysToKeepMessages = (int)$_POST['daystokeepmessages'];
			$externalaccount->MinutesBetweenFetch = (int)$_POST['minutesbetweenfetch'];
			$externalaccount->Port = (int)$_POST['port'];
			$externalaccount->ProcessMIMERecipients = $_POST['processmimerecipients'] == null ? 0 : 1;
			$externalaccount->ProcessMIMEDate = $_POST['processmimedate'] == null ? 0 : 1;
			$externalaccount->ServerAddress = $_POST['serveraddress'];
			$externalaccount->Username = $_POST['username'];
			if($_POST['pwd'])
				$externalaccount->Password = $_POST['pwd'];

			$externalaccount->UseAntiSpam = $_POST['useantispam'] == null ? 0 : 1;
			$externalaccount->UseAntiVirus = $_POST['useantivirus'] == null ? 0 : 1;
			$externalaccount->EnableRouteRecipients = $_POST['enablerouterecipients'] == null ? 0 : 1;
			$externalaccount->ConnectionSecurity = (int)$_POST['connectionsecurity'];
			$externalaccount->Save();
			sendResult(array('eaid' => $externalaccount->ID));
		case 'externalaccount_delete':
			$obAccount->FetchAccounts->DeleteByDBID((int)$_POST['eaid']);
			sendResult(HMS_SUCCESS); 
		case 'externalaccount_download':
			$externalaccount = $obAccount->FetchAccounts->ItemByDBID((int)$_POST['eaid']);
			$externalaccount->DownloadNow();
			sendResult(HMS_SUCCESS);
	}
	sendResult('Action unknown', HMS_ERROR);
}
catch (Exception $e) {
	sendResult(trim(strip_tags($e->getMessage())), HMS_ERROR);
}

function sendResult($message, $error = 0)
{
	$out=array('error' => $error, 'text' => $message);
	exit(serialize($out));
}

function externalaccountsLoad($externalaccounts)
{
	$count = $externalaccounts->Count();
	$data = array();

	for ($i = 0; $i < $count; $i++) {
		$externalaccount = $externalaccounts->Item($i);
		$data[] = array(
			'name'	  => $externalaccount->Name,
			'eaid'	  => $externalaccount->ID,
			'enabled' => $externalaccount->Enabled ?: 0
		);
	}
	return $data;
}
