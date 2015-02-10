<?php

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'contactimporter');

if ( !OW::getConfig()->configExists('contactimporter', 'facebook_app_secret') )
{
    OW::getConfig()->addConfig('contactimporter', 'facebook_app_secret', '', '');
}