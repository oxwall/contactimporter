<?php

$P = OW_DB_PREFIX;
try
{
    Updater::getDbo()->query("DELETE FROM `{$P}usercredits_action` WHERE actionKey='import_friend' AND pluginKey='contactimporter'");
}
catch (  Exception $e )
{}

Updater::getLanguageService()->importPrefixFromZip( dirname(__FILE__) . DS . 'langs.zip', 'contactimporter');