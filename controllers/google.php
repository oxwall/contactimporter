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
class CONTACTIMPORTER_CTRL_Google extends OW_ActionController
{
    public function popup()
    {
	$document = OW::getDocument();
        $document->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));

	if ( isset($_GET['error']) )
	{
		$document->addOnloadScript('window.close();');
		$this->assign('close', true);
		return;
	}

        //setting parameters
        $authcode= $_GET["code"];

        $clientId = OW::getConfig()->getValue('contactimporter', 'google_client_id');
        $clientSecret = OW::getConfig()->getValue('contactimporter', 'google_client_secret');

        $redirectUri = OW::getRouter()->urlForRoute('contact-importer-google-oauth');

        $fields = array(
            'code' => urlencode($authcode),
            'client_id'=>  urlencode($clientId),
            'client_secret'=>  urlencode($clientSecret),
            'redirect_uri'=>  urlencode($redirectUri),
            'grant_type'=>  urlencode('authorization_code')
        );

        //url-ify the data for the POST

        $fieldsString='';

        foreach( $fields as $key => $value )
        {
            $fieldsString .= $key . '=' . $value . '&';
        }

        $fieldsString = rtrim($fieldsString, '&');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL,'https://accounts.google.com/o/oauth2/token');
        curl_setopt($ch,CURLOPT_POST,5);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fieldsString);

        // Set so curl_exec returns the result instead of outputting it.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //to trust any ssl certificates
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        //extracting access_token from response string
        $response=  json_decode($result);

	if ( empty($response->access_token) )
	{
            $authUrl = OW::getRequest()->buildUrlQueryString('https://accounts.google.com/o/oauth2/auth', array(
                'response_type' => 'code',
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'state' => 'contacts',
                'scope' => 'https://www.google.com/m8/feeds/'
            ));

            UTIL_Url::redirect($authUrl);
	}

        $accessToken= $response->access_token;
        //passing accesstoken to obtain contact details
        $resultCount = 100;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, 'https://www.google.com/m8/feeds/contacts/default/full?max-results=' . $resultCount . '&oauth_token=' . $accessToken . '&alt=json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('GData-Version: 2.0'));
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch,CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $jsonResponse = curl_exec($ch);
        
        curl_close($ch);
        
        //$jsonResponse =  file_get_contents('https://www.google.com/m8/feeds/contacts/default/full?max-results=' . $resultCount . '&oauth_token=' . $accessToken . '&alt=json');
	$response = json_decode($jsonResponse, true);

	if ( !empty($response["error"]["message"]) )
	{
		echo $response["error"]["message"];
		exit;
	}

	$out = array();
	$list = $response['feed']['entry'];

        $defaultImage = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();

        $contexId = uniqid('ci');
        $jsArray = array();

        foreach ( $list as $item )
	{
            if ( empty($item['gd$email'][0]['address']) )
            {
                continue;
            }

            $address = $item['gd$email'][0]['address'];
            $image = $item['link'][1]['type'] != 'image/*' ? $defaultImage : $item['link'][1]['href'] . '?oauth_token=' . $accessToken;
            $title = empty($item['title']['$t']) ? $address : $item['title']['$t'];
            $uniqId = uniqid('cii');

            $out[] = array(
                'title' => $title,
                'image' => $image,
                'address' => $address,
                'uniqId' => $uniqId,
                'fields' => empty($item['title']['$t']) ? '' : $address,
                'avatar' => array(
                    'title' => $title,
                    'src' => $image
                )
            );

            $jsArray[$address] = array(
                'linkId' => $uniqId,
                'userId' => $address
            );
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'avatar_user_select.js');
        OW::getDocument()->addOnloadScript("
            var cmp = new AvatarUserSelect(" . json_encode($jsArray) . ", '" . $contexId . "');
            cmp.init();
            OW.registerLanguageKey('base', 'avatar_user_select_empty_list_message', '" . OW::getLanguage()->text('base', 'avatar_user_select_empty_list_message') . "');
         ");

        $this->assign('users', $out);
        $this->assign('contexId', $contexId);

        $countLabel = OW::getLanguage()->text('base', 'avatar_user_list_select_count_label');
        $buttonLabel = OW::getLanguage()->text('base', 'avatar_user_list_select_button_label');

        $langs = array(
            'countLabel' => $countLabel,
            'startCountLabel' => (!empty($countLabel) ? str_replace('#count#', '0', $countLabel) : null ),
            'buttonLabel' => $buttonLabel,
            'startButtonLabel' => str_replace('#count#', '0', $buttonLabel)
        );

        $this->assign('langs', $langs);

        $rsp = json_encode(OW::getRouter()->urlFor('CONTACTIMPORTER_CTRL_Google', 'send'));
        OW::getDocument()->addOnloadScript('OW.bind("base.avatar_user_list_select", function( data ){
            var msg = $("#ci-message").val();
	    var inv = $("#ci-message").attr("inv");

	    msg = inv == msg ? "" : msg;
            window.opener.CONTACTIMPORTER_Google.send(' . $rsp . ', data, msg);
            window.close();
        });');
    }

    public function oauth2callback()
    {
        $redirectUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('CONTACTIMPORTER_CTRL_Google', 'popup'), $_GET);

        $this->redirect($redirectUrl);
    }

    public function send()
    {
        $request = json_decode($_POST['request'], true);
        $userId = OW::getUser()->getId();
        $displayName = BOL_UserService::getInstance()->getDisplayName($userId);

        foreach ( $request['contacts'] as $email )
        {
            $code = UTIL_String::getRandomString(20);
            BOL_UserService::getInstance()->saveUserInvitation($userId, $code);


            $inviteUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_join'), array('code' => $code));

            $assigns = array(
                'url' => $inviteUrl,
                'message' => empty($request['message']) ? '' : $request['message'],
                'user' => $displayName
            );

            $tpl = empty($request['message']) ? 'mail_google_invite' : 'mail_google_invite_msg';

            $mail = OW::getMailer()->createMail();
            $mail->setSubject(OW::getLanguage()->text('contactimporter', 'mail_google_invite_subject', $assigns));
            $mail->setHtmlContent(OW::getLanguage()->text('contactimporter', $tpl . '_html', $assigns));
            $mail->setTextContent(OW::getLanguage()->text('contactimporter', $tpl . '_txt', $assigns));
            $mail->addRecipientEmail($email);

            OW::getMailer()->addToQueue($mail);
        }

        $message = OW::getLanguage()->text('contactimporter', 'google_send_success', array(
           'count' => count($request['contacts'])
        ));

        exit($message);
    }
}
