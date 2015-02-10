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

class CONTACTIMPORTER_CTRL_Facebook extends OW_ActionController
{
    public function canvas()
    {
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate('blank'));
        $requestIds = empty($_GET['request_ids']) ? array() : explode(',', $_GET['request_ids']);

        $appId = OW::getConfig()->getValue('contactimporter', 'facebook_app_id');
        $appSecret = OW::getConfig()->getValue('contactimporter', 'facebook_app_secret');

        if ( empty($appId) || empty($appSecret) )
        {
            $this->assign('content', 'App Secret and App Id are required');
            return;
        }

        $facebook = new Facebook(array(
	    'appId' => $appId,
	    'secret' => $appSecret
	));

	$from = array();
	$inviters = array();
	foreach ( $requestIds as $rid )
	{
	    $request = $facebook->api('/' . $rid);

	    if ($request)
	    {
		$from[$request['from']['id']] = $request['from'];
	    }

	    $data = empty($request['data']) ? array() : json_decode($request['data'], true);
	    if ( !empty($data['userId']) )
	    {
		$inviters[] = $data['userId'];
	    }
	}

	$from = array_reverse($from);

	$inviters = array_unique($inviters);
	$joinData = json_encode(array(
            'inviters' => $inviters,
            'requestIds' => $requestIds
        ));

	$code = base64_encode($joinData);
	$url = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_join'), array('code' => $code));

        $buttonEmbed = OW::getThemeManager()->processDecorator('button', array(
            'langLabel' => 'contactimporter+facebook_canvas_page_visit_btn',
            'onclick' => "window.open('" . $url . "'); return false;"
        ));

	switch ( count($from) )
	{
	    case 1:
		$user = reset($from);
		$content = OW::getLanguage()->text('contactimporter', 'facebook_canvas_page_1', array(
		    'user' => $user['name'],
		    'siteUrl' => $url,
                    'button' => $buttonEmbed
		));
		break;

	    case 2:
		$user1 = reset($from);
		$user2 = next($from);
		$content = OW::getLanguage()->text('contactimporter', 'facebook_canvas_page_2', array(
		    'user1' => $user1['name'],
		    'user2' => $user2['name'],
		    'siteUrl' => $url,
                    'button' => $buttonEmbed
		));
		break;

	    default:
		$user = reset($from);
		$content = OW::getLanguage()->text('contactimporter', 'facebook_canvas_page_x', array(
		    'user' => $user['name'],
		    'count' => count($from) - 1,
		    'siteUrl' => $url,
                    'button' => $buttonEmbed
		));
	}

	$this->assign('content', $content);
    }
}