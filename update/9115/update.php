<?php

Updater::getLanguageService()->deleteLangKey('contactimporter', 'email_send_error_max_limit_message');
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'contactimporter');