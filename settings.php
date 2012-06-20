<?php

$settings->add(new admin_setting_configtext('vidtrans_bingid', get_string('app_id', 'vidtrans'),
                   get_string('app_id', 'vidtrans'), get_string('app_idhelp','vidtrans'), PARAM_RAW));
//ffmpeg for file conversion
$settings->add(new admin_setting_configexecutable('vidtrans_ffmpeg', get_string('ffmpeg', 'vidtrans'),
                   get_string('ffmpeghelp','vidtrans'), ''));
//OGGZ info for file conversoin.
$settings->add(new admin_setting_configexecutable('vidtrans_oggzinfo', get_string('oggz-info', 'vidtrans'),
                   get_string('oggz-infohelp','vidtrans'), ''));

?>