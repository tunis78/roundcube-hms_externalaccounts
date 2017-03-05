<?php

/**
 * hMailserver remote external accounts changer
 *
 * @version 1.1
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
 
$rc_remote_ip = 'YOUR ROUNDCUBE SERVER IP ADDRESS';

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
			$obExternalaccount = $obAccount->FetchAccounts->ItemByDBID((int)$_POST['eaid']);
			$eadata = array(
				'enabled'               => $obExternalaccount->Enabled ?: 0,
				'name'                  => $obExternalaccount->Name,
				'daystokeepmessages'    => $obExternalaccount->DaysToKeepMessages,
				'minutesbetweenfetch'   => $obExternalaccount->MinutesBetweenFetch,
				'port'                  => $obExternalaccount->Port,
				'processmimerecipients' => $obExternalaccount->ProcessMIMERecipients ?: 0,
				'processmimedate'       => $obExternalaccount->ProcessMIMEDate ?: 0,
				'serveraddress'         => $obExternalaccount->ServerAddress,
				'username'              => $obExternalaccount->Username,
				'useantispam'           => $obExternalaccount->UseAntiSpam ?: 0,
				'useantivirus'          => $obExternalaccount->UseAntiVirus ?: 0,
				'enablerouterecipients' => $obExternalaccount->EnableRouteRecipients ?: 0,
				'connectionsecurity'    => $obExternalaccount->ConnectionSecurity
			);
			sendResult($eadata);
		case 'externalaccount_edit':
			if ($eaid = (int)$_POST['eaid'])
				$obExternalaccount = $obAccount->FetchAccounts->ItemByDBID($eaid);
			else
				$obExternalaccount = $obAccount->FetchAccounts->Add();

			$obExternalaccount->Enabled = isset($_POST['enabled']) ?: 0;
			$obExternalaccount->Name = $_POST['name'];
			$obExternalaccount->DaysToKeepMessages = (int)$_POST['daystokeepmessages'];
			$obExternalaccount->MinutesBetweenFetch = (int)$_POST['minutesbetweenfetch'];
			$obExternalaccount->Port = (int)$_POST['port'];
			$obExternalaccount->ProcessMIMERecipients = isset($_POST['processmimerecipients']) ?: 0;
			$obExternalaccount->ProcessMIMEDate = isset($_POST['processmimedate']) ?: 0;
			$obExternalaccount->ServerAddress = $_POST['serveraddress'];
			$obExternalaccount->Username = $_POST['username'];
			if($_POST['pwd'])
				$obExternalaccount->Password = $_POST['pwd'];

			$obExternalaccount->UseAntiSpam = isset($_POST['useantispam']) ?: 0;
			$obExternalaccount->UseAntiVirus = isset($_POST['useantivirus']) ?: 0;
			$obExternalaccount->EnableRouteRecipients = isset($_POST['enablerouterecipients']) ?: 0;
			$obExternalaccount->ConnectionSecurity = (int)$_POST['connectionsecurity'];
			$obExternalaccount->Save();
			sendResult(array('eaid' => $obExternalaccount->ID));
		case 'externalaccount_delete':
			$obAccount->FetchAccounts->DeleteByDBID((int)$_POST['eaid']);
			sendResult(HMS_SUCCESS); 
		case 'externalaccount_download':
			$obExternalaccount = $obAccount->FetchAccounts->ItemByDBID((int)$_POST['eaid']);
			$obExternalaccount->DownloadNow();
			sendResult(HMS_SUCCESS);
	}
	sendResult('Action unknown', HMS_ERROR);
}
catch (Exception $e) {
	sendResult(trim(strip_tags($e->getMessage())), HMS_ERROR);
}

function sendResult($message, $error = 0)
{
	$out = array('error' => $error, 'text' => $message);
	exit(serialize($out));
}

function externalaccountsLoad($obExternalaccounts)
{
	$count = $obExternalaccounts->Count();
	$data = array();

	for ($i = 0; $i < $count; $i++) {
		$obExternalaccount = $obExternalaccounts->Item($i);
		$data[] = array(
			'name'	  => $obExternalaccount->Name,
			'eaid'	  => $obExternalaccount->ID,
			'enabled' => $obExternalaccount->Enabled ?: 0
		);
	}
	return $data;
}
