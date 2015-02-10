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

class CONTACTIMPORTER_CMP_Widget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $event = new BASE_CLASS_EventCollector(CONTACTIMPORTER_CLASS_EventHandler::EVENT_COLLECT_PROVIDERS);
        OW::getEventManager()->trigger($event);
        $providers = $event->getData();

        $btns = array();
        foreach ( $providers as $provider )
        {
            $event = new OW_Event(CONTACTIMPORTER_CLASS_EventHandler::EVENT_RENDER_BUTTON, array(
                'provider' => $provider['key'],
                'callbackUrl' => OW::getRouter()->urlFor('CONTACTIMPORTER_CTRL_Import', 'login', array(
                    'provider' => $provider['key']
                ))
            ));

            OW::getEventManager()->trigger($event);

            $data = $event->getData();

            if ( !empty($data) )
            {
                $btns[] = array_merge(array(
                    'iconUrl' => '',
                    'onclick' => '',
                    'href' => 'javascript://',
                    'class' => '',
                    'id' => 'contactimporter-' . $provider['key'],
                    'markup' => null
                ), $data);
            }
        }
        
        $this->assign('btns', $btns);
        $this->addComponent('eicmp', new CONTACTIMPORTER_CMP_EmailInvite());
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW::getLanguage()->text('contactimporter', 'widget_title'),
            self::SETTING_ICON => self::ICON_ADD
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}