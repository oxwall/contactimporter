<?php

Updater::getLanguageService()->deleteLangKey('contactimporter', 'facebook_after_invite_feedback');
Updater::getLanguageService()->deleteLangKey('contactimporter', 'facebook_app_id');
Updater::getLanguageService()->deleteLangKey('contactimporter', 'facebook_app_secret');
Updater::getLanguageService()->deleteLangKey('contactimporter', 'facebook_app_settings');
Updater::getLanguageService()->deleteLangKey('contactimporter', 'facebook_canvas_page_1');
Updater::getLanguageService()->deleteLangKey('contactimporter', 'facebook_canvas_page_2');
Updater::getLanguageService()->deleteLangKey('contactimporter', 'facebook_canvas_page_visit_btn');
Updater::getLanguageService()->deleteLangKey('contactimporter', 'facebook_canvas_page_x');
Updater::getLanguageService()->deleteLangKey('contactimporter', 'facebook_inv_action');
Updater::getLanguageService()->deleteLangKey('contactimporter', 'facebook_inv_message');
Updater::getLanguageService()->deleteLangKey('contactimporter', 'facebook_inv_text');
Updater::getLanguageService()->deleteLangKey('contactimporter', 'facebook_inv_message_text');

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'contactimporter');

if ( OW::getConfig()->configExists('contactimporter', 'facebook_app_id') )
{
    OW::getConfig()->deleteConfig('contactimporter', 'facebook_app_id', '', '');
}

if ( OW::getConfig()->configExists('contactimporter', 'facebook_app_secret') )
{
    OW::getConfig()->deleteConfig('contactimporter', 'facebook_app_secret', '', '');
}
