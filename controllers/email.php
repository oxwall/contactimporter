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
class CONTACTIMPORTER_CTRL_Email extends OW_ActionController
{
    public function send()
    {
        if( empty($_POST['emailList']) )
        {
            exit(json_encode(array( 'success' => false, 'message' => OW::getLanguage()->text('contactimporter', 'email_send_error_empty_email_list'))));
        }
        
        if( count($_POST['emailList']) > (int)OW::getConfig()->getValue('base', 'user_invites_limit'))
        {
            exit(json_encode(array( 'success' => false, 'message' => OW::getLanguage()->text('contactimporter', 'email_send_error_max_limit_message', array('limit' => (int)OW::getConfig()->getValue('base', 'user_invites_limit'))))));
        }

        $userId = OW::getUser()->getId();
        $displayName = BOL_UserService::getInstance()->getDisplayName($userId);

        $vars = array(
            'inviter' => $displayName,
            'siteName' => OW::getConfig()->getValue('base', 'site_name'),
            'customMessage' => empty($_POST['text']) ? null : trim($_POST['text'])
        );

        foreach ( $_POST['emailList'] as $email )
        {
            $code = UTIL_String::getRandomString(20);
            BOL_UserService::getInstance()->saveUserInvitation($userId, $code);
            $vars['siteInviteURL'] = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_join'), array('code' => $code));

            $mail = OW::getMailer()->createMail();
            $mail->setSubject(OW::getLanguage()->text('contactimporter', 'mail_email_invite_subject', $vars));
            $mail->setHtmlContent(OW::getLanguage()->text('contactimporter', 'mail_email_invite_'. ( empty($_POST['text']) ? '' : 'msg_' ) .'html', $vars));
            $mail->setTextContent(OW::getLanguage()->text('contactimporter', 'mail_email_invite_'. ( empty($_POST['text']) ? '' : 'msg_' ) .'txt', $vars));
            $mail->addRecipientEmail($email);
            OW::getMailer()->addToQueue($mail);
        }

        exit(json_encode(array( 'success' =>true, 'message' => OW::getLanguage()->text('contactimporter', 'email_send_success', array( 'count' => count($_POST['emailList']) )))));
    }
}
