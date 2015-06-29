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

OW::getRouter()->addRoute(new OW_Route('contactimporter_facebook_canvas', 'contactimporter/fbcanvas', 'CONTACTIMPORTER_CTRL_Facebook', 'canvas'));
OW::getRouter()->addRoute(new OW_Route('contactimporter_facebook_settings', 'admin/plugins/contactimporter/facebook', 'CONTACTIMPORTER_CTRL_Admin', 'facebook'));

OW::getRouter()->addRoute(new OW_Route('contactimporter_google_settings', 'admin/plugins/contactimporter/google', 'CONTACTIMPORTER_CTRL_Admin', 'google'));

OW::getRouter()->addRoute(new OW_Route('contactimporter_admin', 'admin/plugins/contactimporter', 'CONTACTIMPORTER_CTRL_Admin', 'admin'));
OW::getRouter()->addRoute(new OW_Route('contact-importer-admin', 'admin/plugins/contactimporter', 'CONTACTIMPORTER_CTRL_Admin', 'admin'));

OW::getRouter()->addRoute(new OW_Route('contact-importer-google-oauth', 'google/oauth', 'CONTACTIMPORTER_CTRL_Google', 'oauth2callback'));

$eventHandler = new CONTACTIMPORTER_CLASS_EventHandler;

OW::getEventManager()->bind(CONTACTIMPORTER_CLASS_EventHandler::EVENT_COLLECT_PROVIDERS, array($eventHandler, 'collectProviders'));
OW::getEventManager()->bind(CONTACTIMPORTER_CLASS_EventHandler::EVENT_RENDER_BUTTON, array($eventHandler, 'buttonRender'));
OW::getEventManager()->bind(OW_EventManager::ON_USER_REGISTER, array($eventHandler, 'onUserRegister'));

OW::getEventManager()->bind(OW_EventManager::ON_JOIN_FORM_RENDER, array($eventHandler, 'onJoinFormRender'));


function contactimporter_add_admin_notification( BASE_CLASS_EventCollector $e )
{
    $language = OW::getLanguage();
    $configs = OW::getConfig()->getValues('contactimporter');

    if ( empty($configs['facebook_app_id']) || empty($configs['google_client_id']) || empty($configs['google_client_secret']) || empty($configs['facebook_app_secret']) )
    {
        $e->add($language->text('contactimporter', 'requires_configuration_message', array( 'settingsUrl' => OW::getRouter()->urlForRoute('contactimporter_admin') )));
    }
}
OW::getEventManager()->bind('admin.add_admin_notification', 'contactimporter_add_admin_notification');

function contactimporter_add_access_exception( BASE_CLASS_EventCollector $e )
{
    $e->add(array('controller' => 'CONTACTIMPORTER_CTRL_Facebook', 'action' => 'canvas'));
}

OW::getEventManager()->bind('base.members_only_exceptions', 'contactimporter_add_access_exception');
OW::getEventManager()->bind('base.password_protected_exceptions', 'contactimporter_add_access_exception');
OW::getEventManager()->bind('base.splash_screen_exceptions', 'contactimporter_add_access_exception');

OW::getApplication()->addHttpsHandlerAttrs('CONTACTIMPORTER_CTRL_Facebook', 'canvas');


/*$credits = new CONTACTIMPORTER_CLASS_Credits();
OW::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));*/