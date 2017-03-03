<?php

/**
 * hMailserver external accounts driver
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

    private function _externalaccounts_load($externalaccounts)
    {
        $count = $externalaccounts->Count();
        $data=array();

        for ($i = 0; $i < $count; $i++) {
            $externalaccount = $externalaccounts->Item($i);
            $data[]=array(
                'name'    => $externalaccount->Name,
                'eaid'    => $externalaccount->ID,
                'enabled' => $externalaccount->Enabled ?: 0
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
        if (strstr($username,'@')){
            $temparr = explode('@', $username);
            $domain = $temparr[1];
        }
        else {
            $domain = $rcmail->config->get('username_domain',false);
            if (!$domain) {
                rcube::write_log('errors','Plugin hms_externalaccounts (hmail driver): $config[\'username_domain\'] is not defined.');
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
                    $externalaccount = $obAccount->FetchAccounts->ItemByDBID($data['eaid']);
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
                    return $eadata;
                case 'externalaccount_edit':
                    if ($data['eaid'])
                        $externalaccount = $obAccount->FetchAccounts->ItemByDBID($data['eaid']);
                    else
                        $externalaccount = $obAccount->FetchAccounts->Add();

                    $externalaccount->Enabled = $data['enabled'] == null ? 0 : 1;
                    $externalaccount->Name = $data['name'];
                    $externalaccount->DaysToKeepMessages = (int)$data['daystokeepmessages'];
                    $externalaccount->MinutesBetweenFetch = (int)$data['minutesbetweenfetch'];
                    $externalaccount->Port = (int)$data['port'];
                    $externalaccount->ProcessMIMERecipients = $data['processmimerecipients'] == null ? 0 : 1;
                    $externalaccount->ProcessMIMEDate = $data['processmimedate'] == null ? 0 : 1;
                    $externalaccount->ServerAddress = $data['serveraddress'];
                    $externalaccount->Username = $data['username'];
                    if($data['pwd'])
                        $externalaccount->Password = $data['pwd'];

                    $externalaccount->UseAntiSpam = $data['useantispam'] == null ? 0 : 1;
                    $externalaccount->UseAntiVirus = $data['useantivirus'] == null ? 0 : 1;
                    $externalaccount->EnableRouteRecipients = $data['enablerouterecipients'] == null ? 0 : 1;
                    $externalaccount->ConnectionSecurity = (int)$data['connectionsecurity'];
                    $externalaccount->Save();
                    return array('eaid' => $externalaccount->ID);
                case 'externalaccount_delete':
                    $obAccount->FetchAccounts->DeleteByDBID($data['eaid']);
                    return HMS_SUCCESS; 
                case 'externalaccount_download':
                    $externalaccount = $obAccount->FetchAccounts->ItemByDBID($data['eaid']);
                    $externalaccount->DownloadNow();
                    return HMS_SUCCESS; 
            }

        }
        catch (Exception $e) {
            rcube::write_log('errors', 'Plugin hms_externalaccounts (hmail driver): ' . trim(strip_tags($e->getMessage())));
            rcube::write_log('errors', 'Plugin hms_externalaccounts (hmail driver): This problem is often caused by Authenticate permissions.');
            return HMS_ERROR;
        }
    }
}
