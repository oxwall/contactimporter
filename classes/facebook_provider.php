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

require_once OW_DIR_LIB . 'facebook' . DS . 'facebook.php';

class CONTACTIMPORTER_CLASS_FacebookProvider extends CONTACTIMPORTER_CLASS_Provider
{
    public function __construct()
    {
        $staticUrl = OW::getPluginManager()->getPlugin('contactimporter')->getStaticUrl();

        parent::__construct(array(
            'key' => 'facebook',
            'title' => 'Facebook',
            'settigsUrl' => OW::getRouter()->urlForRoute('contactimporter_facebook_settings'),
            'iconClass' => 'ow_ic_gear_wheel'
        ));
    }

    public function prepareButton( $params )
    {
        $appId = OW::getConfig()->getValue('contactimporter', 'facebook_app_id');

        if ( empty($appId) )
        {
            return;
        }

        $staticUrl = OW::getPluginManager()->getPlugin('contactimporter')->getStaticUrl();
        $document = OW::getDocument();
        $document->addScript($staticUrl . 'js/facebook.js');

        $userId = OW::getUser()->getId();
        $fbLibUrl = '//connect.facebook.net/en_US/all.js';
        
        $code = UTIL_String::getRandomString(20);
        BOL_UserService::getInstance()->saveUserInvitation($userId, $code);
        $urlForInvite = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_join'), array('code' => $code));

        $js = UTIL_JsGenerator::newInstance();
        $js->newObject(array('window', 'CONTACTIMPORTER_FaceBook'), 'CI_Facebook', array($fbLibUrl, $userId, $urlForInvite));

        $fbParams = array(
            'appId' => $appId,
            'status' => true, // check login status
            'cookie' => true, // enable cookies to allow the server to access the session
            'xfbml'  => true
        );

        $js->callFunction(array('CONTACTIMPORTER_FaceBook', 'init'), array($fbParams));
        $document->addOnloadScript((string) $js);

	OW::getLanguage()->addKeyForJs('contactimporter', 'facebook_inv_message_text');
        OW::getLanguage()->addKeyForJs('contactimporter', 'facebook_after_invite_feedback');

        return array(
            'iconUrl' => $staticUrl . 'img/f.png',
            'onclick' => "CONTACTIMPORTER_FaceBook.request(); return false;"
        );
    }

    public function getInviters( $code )
    {
	$data = base64_decode($code);
        $data = json_decode($data, true);

        $requestIds = empty($data['requestIds']) ? array() : $data['requestIds'];

        if ( !empty($requestIds) )
        {
            $appId = OW::getConfig()->getValue('contactimporter', 'facebook_app_id');
            $appSecret = OW::getConfig()->getValue('contactimporter', 'facebook_app_secret');
            $facebook = new Facebook(array(
                'appId' => $appId,
                'secret' => $appSecret
            ));

            foreach ( $requestIds as $id )
            {
                try
                {
                    $facebook->api('/' . $id, 'DELETE');
                }
                catch ( Exception $e )
                {}
            }
        }

        return empty($data['inviters']) ? array() : $data['inviters'];
    }
}