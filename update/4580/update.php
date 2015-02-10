<?php

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'contactimporter');

if ( !OW::getConfig()->configExists('contactimporter', 'facebook_app_id') )
{
    OW::getConfig()->addConfig('contactimporter', 'facebook_app_id', '', '');
}

if ( !OW::getConfig()->configExists('contactimporter', 'google_site_id') )
{
    OW::getConfig()->addConfig('contactimporter', 'google_site_id', '', '');
}