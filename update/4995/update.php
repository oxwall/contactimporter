<?php

if ( !UPDATER::getConfigService()->configExists('contactimporter', 'google_client_id') )
{
    UPDATER::getConfigService()->addConfig('contactimporter', 'google_client_id', '');
}

if ( !UPDATER::getConfigService()->configExists('contactimporter', 'google_client_secret') )
{
    UPDATER::getConfigService()->addConfig('contactimporter', 'google_client_secret', '');
}

Updater::getLanguageService()->importPrefixFromZip( dirname(__FILE__) . DS . 'langs.zip', 'contactimporter');