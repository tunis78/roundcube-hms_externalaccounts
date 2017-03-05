<?php

/**
 * hMailserver external accounts driver
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

class rcube_hmail_externalaccounts
{
    
    public function load($data)
    {
        return $this->data_handler($data);
    }

    public function save($data)
    {
        return $this->data_handler($data);
    }

    private function _externalaccounts_load($obExternalaccounts)
    {
        $count = $obExternalaccounts->Count();
        $data = array();

        for ($i = 0; $i < $count; $i++) {
            $obExternalaccount = $obExternalaccounts->Item($i);
            $data[] = array(
                'name'    => $obExternalaccount->Name,
                'eaid'    => $obExternalaccount->ID,
                'enabled' => $obExternalaccount->Enabled ?: 0
            );
        }

        return $data;
    }

    private function data_handler($data)
    {
        $rcmail = rcmail::get_instance();

        try {
            $remote = $rcmail->config->get('hms_externalaccounts_remote_dcom', false);
            if ($remote)
                $obApp = new COM("hMailServer.Application", $rcmail->config->get('hms_externalaccounts_remote_server'), CP_UTF8);
            else
                $obApp = new COM("hMailServer.Application", NULL, CP_UTF8);
        }
        catch (Exception $e) {
            rcube::write_log('errors', 'Plugin hms_externalaccounts (hmail driver): ' . trim(strip_tags($e->getMessage())));
            rcube::write_log('errors', 'Plugin hms_externalaccounts (hmail driver): This problem is often caused by DCOM permissions not being set.');
            return HMS_ERROR;
        }

        $username = $rcmail->user->data['username'];
        if (strstr($username, '@')){
            $temparr = explode('@', $username);
            $domain = $temparr[1];
        }
        else {
            $domain = $rcmail->config->get('username_domain', false);
            if (!$domain) {
                rcube::write_log('errors', 'Plugin hms_externalaccounts (hmail driver): $config[\'username_domain\'] is not defined.');
                return HMS_ERROR;
            }
            $username = $username . '@' . $domain;
        }

        $password = $rcmail->decrypt($_SESSION['password']);

        $obApp->Authenticate($username, $password);
        try {
            $obAccount = $obApp->Domains->ItemByName($domain)->Accounts->ItemByAddress($username);

            switch($data['action']){
                case 'externalaccounts_load':
                    return $this->_externalaccounts_load($obAccount->FetchAccounts());
                case 'externalaccount_load':
                    $obExternalaccount = $obAccount->FetchAccounts->ItemByDBID($data['eaid']);
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
                    return $eadata;
                case 'externalaccount_edit':
                    if ($eaid = (int)$data['eaid'])
                        $obExternalaccount = $obAccount->FetchAccounts->ItemByDBID($eaid);
                    else
                        $obExternalaccount = $obAccount->FetchAccounts->Add();

                    $obExternalaccount->Enabled = $data['enabled'] == null ? 0 : 1;
                    $obExternalaccount->Name = $data['name'];
                    $obExternalaccount->DaysToKeepMessages = (int)$data['daystokeepmessages'];
                    $obExternalaccount->MinutesBetweenFetch = (int)$data['minutesbetweenfetch'];
                    $obExternalaccount->Port = (int)$data['port'];
                    $obExternalaccount->ProcessMIMERecipients = $data['processmimerecipients'] == null ? 0 : 1;
                    $obExternalaccount->ProcessMIMEDate = $data['processmimedate'] == null ? 0 : 1;
                    $obExternalaccount->ServerAddress = $data['serveraddress'];
                    $obExternalaccount->Username = $data['username'];
                    if($data['pwd'])
                        $obExternalaccount->Password = $data['pwd'];

                    $obExternalaccount->UseAntiSpam = $data['useantispam'] == null ? 0 : 1;
                    $obExternalaccount->UseAntiVirus = $data['useantivirus'] == null ? 0 : 1;
                    $obExternalaccount->EnableRouteRecipients = $data['enablerouterecipients'] == null ? 0 : 1;
                    $obExternalaccount->ConnectionSecurity = (int)$data['connectionsecurity'];
                    $obExternalaccount->Save();
                    return array('eaid' => $obExternalaccount->ID);
                case 'externalaccount_delete':
                    $obAccount->FetchAccounts->DeleteByDBID((int)$data['eaid']);
                    return HMS_SUCCESS; 
                case 'externalaccount_download':
                    $obExternalaccount = $obAccount->FetchAccounts->ItemByDBID((int)$data['eaid']);
                    $obExternalaccount->DownloadNow();
                    return HMS_SUCCESS; 
            }
            return HMS_ERROR;
        }
        catch (Exception $e) {
            rcube::write_log('errors', 'Plugin hms_externalaccounts (hmail driver): ' . trim(strip_tags($e->getMessage())));
            rcube::write_log('errors', 'Plugin hms_externalaccounts (hmail driver): This problem is often caused by Authenticate permissions.');
            return HMS_ERROR;
        }
    }
}
