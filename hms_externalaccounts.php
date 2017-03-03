<?php

/**
 * hMailServer External Accounts Plugin for Roundcube
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

define('HMS_ERROR', 1);
define('HMS_CONNECT_ERROR', 2);
define('HMS_SUCCESS', 0);

/**
 * Change hMailServer external accounts plugin
 *
 * Plugin that adds functionality to edit hMailServer external accounts.
 * It provides common functionality and user interface and supports
 * several backends to finally update the external accounts.
 *
 * For installation and configuration instructions please read the README file.
 *
 * @author Andreas Tunberg
 */
 
class hms_externalaccounts extends rcube_plugin
{
    public $task = "settings";
    private $rc;
    private $driver;
    private $eaid;
    private $reload = false;
    public $steptitle;

    function init()
    {

        $this->rc = rcube::get_instance();
        $this->load_config();
        $this->add_texts('localization/');
        $this->include_stylesheet($this->local_skin_path() . '/hms_externalaccounts.css');
        $this->include_script('hms_externalaccounts.js');

        $this->register_action('plugin.externalaccounts', array($this, 'externalaccounts'));
        $this->register_action('plugin.externalaccounts-edit', array($this, 'externalaccounts_edit'));
        $this->register_action('plugin.externalaccounts-actions', array($this, 'externalaccounts_actions'));
        
        $this->add_hook('settings_actions', array($this, 'settings_actions'));
    }

    function settings_actions($args)
    {
        $args['actions'][] = array(
            'action' => 'plugin.externalaccounts',
            'class'  => 'externalaccounts',
            'label'  => 'externalaccounts',
            'title'  => 'editexternalaccounts',
            'domain' => 'hms_externalaccounts'
        );

        return $args;
    }

    function steptitle()
    {
        return $this->gettext($this->steptitle);
    }

    function externalaccounts()
    {
        if ($eaid = rcube_utils::get_input_value('_eaid', rcube_utils::INPUT_GET)) {
            $this->rc->output->set_env('externalaccounts_selected', $eaid);
        }

        $this->rc->output->add_handlers(array(
            'externalaccountframe' => array($this, 'externalaccounts_frame'),
            'externalaccountslist' => array($this, 'externalaccounts_list'),
        ));

        $this->rc->output->include_script('list.js');

        $this->rc->output->set_pagetitle($this->gettext('editexternalaccounts'));
        $this->rc->output->add_label('hms_externalaccounts.externalaccountdeleteconfirm', 'hms_externalaccounts.disabled');

        $this->rc->output->send('hms_externalaccounts.externalaccounts');
    }

    function externalaccounts_actions()
    {
        if (($eaid = rcube_utils::get_input_value('_eaid', rcube_utils::INPUT_POST)) && ($action = rcube_utils::get_input_value('_act', rcube_utils::INPUT_POST))) {
            
            if ($action == 'delete') {
                $result = $this->_save(array('action' => 'externalaccount_delete', 'eaid' => $eaid));

                if (!$result) {
                    $this->rc->output->command('plugin.externalaccounts-reload');
                    $this->rc->output->command('display_message', $this->gettext('externalaccountsuccessfullyupdated'), 'confirmation');
                }
                else {
                    $this->rc->output->command('display_message', $result, 'error');
                }
            }
            else {
                $result = $this->_save(array('action' => 'externalaccount_download', 'eaid' => $eaid));

                if (!$result) {
                    $this->rc->output->command('display_message', $this->gettext('externalaccountnowdownloading'), 'confirmation');
                }
                else {
                    $this->rc->output->command('display_message', $result, 'error');
                }
            }
        }
    }

    function externalaccounts_edit()
    {
        if (!empty($_GET['_eaid']) || !empty($_POST['_eaid'])) {
            $this->eaid = rcube_utils::get_input_value('_eaid', rcube_utils::INPUT_GPC);
        }

        if ($action = rcube_utils::get_input_value('_act', rcube_utils::INPUT_GPC)) {

            $daystokeepmessages = rcube_utils::get_input_value('_daystokeepmessages', rcube_utils::INPUT_POST);
            if ($daystokeepmessages == 1)
                $daystokeepmessages = rcube_utils::get_input_value('_daystokeepmessagesvalue', rcube_utils::INPUT_POST);

            $dataToSave = array(
                'action'                => 'externalaccount_edit',
                'eaid'                  => $this->eaid,
                'enabled'               => rcube_utils::get_input_value('_enabled', rcube_utils::INPUT_POST),
                'name'                  => rcube_utils::get_input_value('_name', rcube_utils::INPUT_POST),
                'daystokeepmessages'    => $daystokeepmessages,
                'minutesbetweenfetch'   => rcube_utils::get_input_value('_minutesbetweenfetch', rcube_utils::INPUT_POST),
                'port'                  => rcube_utils::get_input_value('_port', rcube_utils::INPUT_POST),
                'processmimerecipients' => rcube_utils::get_input_value('_processmimerecipients', rcube_utils::INPUT_POST),
                'processmimedate'       => rcube_utils::get_input_value('_processmimedate', rcube_utils::INPUT_POST),
                'serveraddress'         => rcube_utils::get_input_value('_serveraddress', rcube_utils::INPUT_POST),
                'username'              => rcube_utils::get_input_value('_username', rcube_utils::INPUT_POST),
                'pwd'                   => rcube_utils::get_input_value('_password', rcube_utils::INPUT_POST),
                'useantispam'           => rcube_utils::get_input_value('_useantispam', rcube_utils::INPUT_POST),
                'useantivirus'          => rcube_utils::get_input_value('_useantivirus', rcube_utils::INPUT_POST),
                'enablerouterecipients' => rcube_utils::get_input_value('_enablerouterecipients', rcube_utils::INPUT_POST),
                'connectionsecurity'    => rcube_utils::get_input_value('_connectionsecurity', rcube_utils::INPUT_POST)
            );
            $result = $this->_save($dataToSave, true);

            if (!$result || is_array($result)) {
                $this->rc->output->command('display_message', $this->gettext('externalaccountsuccessfullyupdated'), 'confirmation');
                $this->reload = true;
                if (!$this->eaid) $this->eaid = $result['eaid'];
            }
            else {
                if ($result == HMS_CONNECT_ERROR) {
                    $error = $this->gettext('updateconnecterror');
                } else {
                    $error = $this->gettext('updateerror');
                }
                $this->rc->output->command('display_message', $error, 'error');
            }

        }
        $this->steptitle = $this->eaid ? 'editexternalaccount' : 'newexternalaccount';

        $this->register_handler('plugin.steptitle', array($this, 'steptitle'));
        $this->register_handler('plugin.externalaccountform', array($this, 'externalaccount_edit'));
        $this->rc->output->send('hms_externalaccounts.externalaccountedit');
    }

    function externalaccount_edit()
    {
        if ($this->reload) {
            $this->rc->output->set_env('externalaccounts_reload', $this->eaid); 
            return;
        }

        if($this->eaid){
            $currentData = $this->_load(array('action' => 'externalaccount_load', 'eaid' => $this->eaid));

            if (!is_array($currentData)) {
                if ($currentData == HMS_CONNECT_ERROR) {
                    $error = $this->gettext('loadconnecterror');
                }
                else {
                    $error = $this->gettext('loaderror');
                }

                $this->rc->output->command('display_message', $error, 'error');
                return;
            }
        } else {
            $currentData = array(
                'enabled'               => 0,
                'name'                  => '',
                'daystokeepmessages'    => 0,
                'minutesbetweenfetch'   => 30,
                'port'                  => 110,
                'processmimerecipients' => 0,
                'processmimedate'       => 0,
                'serveraddress'         => '',
                'username'              => '',
                'useantispam'           => 0,
                'useantivirus'          => 0,
                'enablerouterecipients' => 0,
                'connectionsecurity'    => 0
            );
        }

        $input_act = new html_hiddenfield(array (
                'name'  => '_act',
                'value' => 'save'
        ));

        $input_eaid = new html_hiddenfield(array (
                'name'  => '_eaid',
                'value' => $this->eaid
        ));


        $table = new html_table(array('cols' => 2, 'class' => 'propform'));

        $field_id = 'enabled';
        $input_enabled = new html_checkbox(array (
                'name'  => '_enabled',
                'id'    => $field_id,
                'value' => 1
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('enabled'))));
        $table->add(null, $input_enabled->show($currentData['enabled']));

        $field_id = 'name';
        $input_name = new html_inputfield(array (
                'type'      => 'text',
                'name'      => '_name',
                'id'        => $field_id,
                'maxlength' => 255
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('name'))));
        $table->add(null, $input_name->show($currentData['name']));


        $legend = html::tag('legend', array(), rcube::Q($this->gettext('externalaccountsettings')));
        $fieldsets = html::tag('fieldset', array(), $legend . $table->show());


        $table = new html_table(array('cols' => 2, 'class' => 'propform'));

        $select_type = new html_select(array (
                'name' => '_type'
        ));
        $select_type->add('POP3', 0);

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('type'))));
        $table->add(null, $select_type->show(0));

        $field_id = 'serveraddress';
        $input_serveraddress = new html_inputfield(array (
                'type'      => 'text',
                'name'      => '_serveraddress',
                'id'        => $field_id,
                'maxlength' => 255
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('serveraddress'))));
        $table->add(null, $input_serveraddress->show($currentData['serveraddress']));

        $field_id = 'port';
        $input_port = new html_inputfield(array (
                'type'      => 'text',
                'name'      => '_port',
                'id'        => $field_id,
                'maxlength' => 11,
                'size'      => 5
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('tcp/ipport'))));
        $table->add(null, $input_port->show($currentData['port']));

        $select_connectionsecurity = new html_select(array (
                'name' => '_connectionsecurity'
        ));
        $select_connectionsecurity->add($this->gettext('none'), 0);
        $select_connectionsecurity->add($this->gettext('starttlsrequired'), 3);
        $select_connectionsecurity->add($this->gettext('ssl/tls'), 1);

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('connectionsecurity'))));
        $table->add(null, $select_connectionsecurity->show($currentData['connectionsecurity']));

        $field_id = 'username';
        $input_username = new html_inputfield(array (
                'type'      => 'text',
                'name'      => '_username',
                'id'        => $field_id,
                'maxlength' => 255
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('username'))));
        $table->add(null, $input_username->show($currentData['username']));

        $field_id = 'password';
        $input_password = new html_passwordfield(array (
                'name'      => '_password',
                'id'        => $field_id,
                'maxlength' => 255
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('password'))));
        $table->add(null, $input_password->show());


        $legend = html::tag('legend', array(), rcube::Q($this->gettext('serverinformation')));
        $fieldsets .= html::tag('fieldset', array(), $legend . $table->show());


        $table = new html_table(array('cols' => 2, 'class' => 'propform'));

        $field_id = 'minutesbetweenfetch';
        $input_minutesbetweenfetch = new html_inputfield(array (
                'type'      => 'text',
                'name'      => '_minutesbetweenfetch',
                'id'        => $field_id,
                'maxlength' => 11,
                'size'      => 3
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('minutesbetweendownload'))));
        $table->add(null, $input_minutesbetweenfetch->show($currentData['minutesbetweenfetch']));

        $field_id = 'processmimerecipients';
        $input_processmimerecipients = new html_checkbox(array (
                'name'  => '_processmimerecipients',
                'id'    => $field_id,
                'value' => 1
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('delivertorecipientsinmimeheaders'))));
        $table->add(null, $input_processmimerecipients->show($currentData['processmimerecipients']));

        $field_id = 'processmimedate';
        $input_processmimedate = new html_checkbox(array (
                'name'  => '_processmimedate',
                'id'    => $field_id,
                'value' => 1
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('retrievedatefromreceivedheader'))));
        $table->add(null, $input_processmimedate->show($currentData['processmimedate']));

        $field_id = 'useantispam';
        $input_useantispam = new html_checkbox(array (
                'name'  => '_useantispam',
                'id'    => $field_id,
                'value' => 1
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('antispam'))));
        $table->add(null, $input_useantispam->show($currentData['useantispam']));

        $field_id = 'useantivirus';
        $input_useantivirus = new html_checkbox(array (
                'name'  => '_useantivirus',
                'id'    => $field_id,
                'value' => 1
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('antivirus'))));
        $table->add(null, $input_useantivirus->show($currentData['useantivirus']));

        $field_id = 'enablerouterecipients';
        $input_enablerouterecipients = new html_checkbox(array (
                'name'  => '_enablerouterecipients',
                'id'    => $field_id,
                'value' => 1
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('allowrouterecipients'))));
        $table->add(null, $input_enablerouterecipients->show($currentData['enablerouterecipients']));


        $field_id = 'deletemessagesimmediately';
        $input_deletemessagesimmediately = new html_radiobutton(array (
                'name'  => '_daystokeepmessages',
                'id'    => $field_id,
                'value' => -1
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('deletemessagesimmediately'))));
        $table->add(null, $input_deletemessagesimmediately->show($currentData['daystokeepmessages']));

        $field_id = 'donotdeletemessages';
        $input_donotdeletemessages = new html_radiobutton(array (
                'name'  => '_daystokeepmessages',
                'id'    => $field_id,
                'value' => 0
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('donotdeletemessages'))));
        $table->add(null, $input_donotdeletemessages->show($currentData['daystokeepmessages']));

        $field_id = 'deletemessagesafter';
        $input_deletemessagesafter = new html_radiobutton(array (
                'name'  => '_daystokeepmessages',
                'id'    => $field_id,
                'value' => 1
        ));

        $input_deletemessagesaftervalue = new html_inputfield(array (
                'type'      => 'text',
                'name'      => '_daystokeepmessagesvalue',
                'id'        => $field_id,
                'maxlength' => 11,
                'size'      => 3
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('deletemessagesafter'))));
        $table->add(null, $input_deletemessagesafter->show($currentData['daystokeepmessages'] > 0 ? 1 : 0) . '&nbsp;' . $input_deletemessagesaftervalue->show($currentData['daystokeepmessages'] > 0 ? $currentData['daystokeepmessages'] : '') . '&nbsp;' . rcube::Q($this->gettext('days')));


        $legend = html::tag('legend', array(), rcube::Q($this->gettext('settings')));
        $fieldsets .= html::tag('fieldset', array(), $legend . $table->show());


        $form = $this->rc->output->form_tag(array(
            'id'     => 'externalaccount-form',
            'name'   => 'externalaccount-form',
            'class'  => 'propform',
            'method' => 'post',
            'action' => './?_task=settings&_action=plugin.externalaccounts-edit',
        ), $input_act->show() . $input_eaid->show() . $fieldsets );

        $this->rc->output->add_gui_object('externalaccountform', 'externalaccount-form');

        return $form;
    }

    function externalaccounts_list($attrib)
    {

        $attrib += array('id' => 'rcmExternalaccountsList', 'tagname' => 'table');

        $plugin = $this->rc->plugins->exec_hook('externalaccount_list', array(
            'list' => $this->list_externalaccounts(),
            'cols' => array('name')
        ));

        $out = $this->rc->table_output($attrib, $plugin['list'], $plugin['cols'], 'eaid');

        $disabled_externalaccounts = array();
        foreach ($plugin['list'] as $item) {
            if (!$item['enabled']) {
                $disabled_externalaccounts[] = $item['eaid'];
            }
        }

        $this->rc->output->add_gui_object('externalaccountlist', $attrib['id']);
        $this->rc->output->set_env('disabled_externalaccounts', $disabled_externalaccounts);

        return $out;
    }

    function externalaccounts_frame($attrib)
    {
        if (!$attrib['id']) {
            $attrib['id'] = 'rcmExternalaccountFrame';
        }

        $this->rc->output->set_env('contentframe', $attrib['id']);

        return $this->rc->output->frame($attrib, true);
    }

    function list_externalaccounts()
    {
        $currentData = $this->_load(array('action' => 'externalaccounts_load'));

        if (!is_array($currentData)) {
            if ($currentData == HMS_CONNECT_ERROR) {
                $error = $this->gettext('loadconnecterror');
            }
            else {
                $error = $this->gettext('loaderror');
            }

            $this->rc->output->command('display_message', $error, 'error');
            return array();
        }

        return $currentData;
    }


    private function _load($data)
    {
        if (is_object($this->driver)) {
            $result = $this->driver->load($data);
        }
        elseif (!($result = $this->load_driver())){
            $result = $this->driver->load($data);
        }
        return $result;
    }

    private function _save($data,$response = false)
    {
        if (is_object($this->driver)) {
            $result = $this->driver->save($data);
        }
        elseif (!($result = $this->load_driver())){
            $result = $this->driver->save($data);
        }

        if ($response) return $result;

        switch ($result) {
            case HMS_SUCCESS:
                return;
            case HMS_CONNECT_ERROR:
                $reason = $this->gettext('updateconnecterror');
                break;
            case HMS_ERROR:
            default:
                $reason = $this->gettext('updateerror');
        }
        return $reason;
    }

    private function load_driver()
    {
        $driver = $this->rc->config->get('hms_externalaccounts_driver', 'hmail');
        $class  = "rcube_{$driver}_externalaccounts";
        $file   = $this->home . "/drivers/$driver.php";

        if (!file_exists($file)) {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "hms_externalaccounts plugin: Unable to open driver file ($file)"
            ), true, false);
            return HMS_ERROR;
        }

        include_once $file;

        if (!class_exists($class, false) || !method_exists($class, 'save') || !method_exists($class, 'load')) {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "hms_externalaccounts plugin: Broken driver $driver"
            ), true, false);
            return $this->gettext('internalerror');
        }

        $this->driver = new $class;
    }
}
