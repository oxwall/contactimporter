<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @author Kambalin Sergey <greyexpert@gmail.com>
 * @package ow.ow_plugins.contact_importer
 * @since 1.0
 */

class CONTACTIMPORTER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function admin()
    {
        $event = new BASE_CLASS_EventCollector(CONTACTIMPORTER_CLASS_EventHandler::EVENT_COLLECT_PROVIDERS);
        OW::getEventManager()->trigger($event);
        $providers = $event->getData();
        $firstProvider = reset($providers);

        $this->redirect($firstProvider['settigsUrl']);
    }

    public function facebook( $params )
    {
        $this->addComponent('menu', new CONTACTIMPORTER_CMP_AdminTabs());

        $appId = OW::getConfig()->getValue('contactimporter', 'facebook_app_id');
        $appSecret = OW::getConfig()->getValue('contactimporter', 'facebook_app_secret');

        $form = new Form('fasebook_settings');

        $element = new TextField('appId');
        $element->setLabel(OW::getLanguage()->text('contactimporter', 'facebook_app_id'));
        $element->setRequired(true);
        $element->setValue($appId);
        $form->addElement($element);

        $element = new TextField('appSecret');
        $element->setLabel(OW::getLanguage()->text('contactimporter', 'facebook_app_secret'));
        $element->setRequired(true);
        $element->setValue($appSecret);
        $form->addElement($element);

        $element = new Submit('save');
        $element->setValue(OW::getLanguage()->text('contactimporter', 'save_btn_label'));

        $form->addElement($element);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $value = trim($form->getElement('appId')->getValue());
            OW::getConfig()->saveConfig('contactimporter', 'facebook_app_id', $value);

            $value = trim($form->getElement('appSecret')->getValue());
            OW::getConfig()->saveConfig('contactimporter', 'facebook_app_secret', $value);

            OW::getFeedback()->info(OW::getLanguage()->text('contactimporter', 'admin_settings_updated'));

            $this->redirect();
        }

        $this->addForm($form);

        $manualUrl = 'http://docs.oxwall.org/plugin-tuts:contact-importer#facebook';


        if ( defined('OW_PLUGIN_XP') )
        {
            $manualUrl = 'http://docs.wall.fm/faq:site_configuration:contact_importer_setup#facebook_application_configuration';
        }

        $this->assign('manualUrl', $manualUrl);
    }

    public function google( $params )
    {
        $this->addComponent('menu', new CONTACTIMPORTER_CMP_AdminTabs());

        $clientId = OW::getConfig()->getValue('contactimporter', 'google_client_id');
        $clientSecret = OW::getConfig()->getValue('contactimporter', 'google_client_secret');

        $form = new Form('google_settings');

        $element = new TextField('clientId');
        $element->setLabel(OW::getLanguage()->text('contactimporter', 'google_client_id'));
        $element->setRequired(true);
        $element->setValue($clientId);
        $form->addElement($element);

        $element = new TextField('clientSecret');
        $element->setLabel(OW::getLanguage()->text('contactimporter', 'google_client_secret'));
        $element->setRequired(true);
        $element->setValue($clientSecret);
        $form->addElement($element);

        $element = new Submit('save');
        $element->setValue(OW::getLanguage()->text('contactimporter', 'save_btn_label'));

        $form->addElement($element);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            OW::getConfig()->saveConfig('contactimporter', 'google_client_id', trim($form->getElement('clientId')->getValue()));
            OW::getConfig()->saveConfig('contactimporter', 'google_client_secret', trim($form->getElement('clientSecret')->getValue()));

            OW::getFeedback()->info(OW::getLanguage()->text('contactimporter', 'admin_settings_updated'));

            $this->redirect();
        }

        $this->addForm($form);

        $manualUrl = 'http://docs.oxwall.org/plugin-tuts:contact-importer#google';

        if ( defined('OW_PLUGIN_XP') )
        {
            $manualUrl = 'http://docs.wall.fm/faq:site_configuration:contact_importer_setup#google_application_configuration';
        }

        $this->assign('manualUrl', $manualUrl);
    }
}