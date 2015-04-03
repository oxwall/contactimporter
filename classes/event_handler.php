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

class CONTACTIMPORTER_CLASS_EventHandler
{
    const EVENT_COLLECT_PROVIDERS = 'contactimporter.collect_providers';
    const EVENT_RENDER_BUTTON = 'contactimporter.render_button';

    private $providers = array();

    public function __construct()
    {
        $this->providers['facebook'] = new CONTACTIMPORTER_CLASS_FacebookProvider();
        $this->providers['google'] = new CONTACTIMPORTER_CLASS_GoogleProvider();
        $this->providers['email'] = new CONTACTIMPORTER_CLASS_EmailProvider();
    }

    public function collectProviders( BASE_CLASS_EventCollector $event )
    {
        foreach ( $this->providers as $p )
        {
            $event->add($p->getProviderInfo());
        }
    }

    public function buttonRender( OW_Event $event )
    {
        $params = $event->getParams();
        $key = $params['provider'];

        if ( empty ($this->providers[$key]) )
        {
            return;
        }

        /* @var $provider CONTACTIMPORTER_CLASS_Provider */
        $provider = $this->providers[$key];
        $data = $provider->prepareButton($params);

        $event->setData($data);
    }

    public function onUserRegister( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['params']['code']) )
        {
            return;
        }

	$userIds = array();

	foreach ( $this->providers as $provider )
	{
	    $inviters = $provider->getInviters($params['params']['code']);
	    if ( $inviters && is_array($inviters) )
	    {
		$userIds = array_merge($userIds, $inviters);
	    }
	}
        
        $newId = $params['userId'];

	foreach ( $userIds as $uid )
	{
            $event = new OW_Event('friends.add_friend', array(
                'requesterId' => $uid,
                'userId' => $newId
            ));

            OW::getEventManager()->trigger($event);

	    /*$eventParams = array('pluginKey' => 'contactimporter', 'action' => 'import_friend', 'userId' => $userId);

	    if ( OW::getEventManager()->call('usercredits.check_balance', $eventParams) === true )
	    {
		OW::getEventManager()->call('usercredits.track_action', $eventParams);
	    }*/
	}
    }
    
    public function onJoinFormRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['code']) )
        {
            $data = base64_decode($params['code']);
            $data = json_decode($data, true);
            
            if ( !empty($data['inviters']) )
            {
                throw new JoinRenderException();
            }
        }
    }
}
