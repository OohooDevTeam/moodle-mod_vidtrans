<?php
$settings->add(new admin_setting_configtext('vidtrans_client_id', get_string('client_id',
                        'vidtrans'),
                get_string('client_idhelp', 'vidtrans'), ''));
$settings->add(new admin_setting_configtext('vidtrans_client_secret', get_string('client_secret',
                        'vidtrans'),
                get_string('client_secrethelp', 'vidtrans'), ''));

//ffmpeg for file conversion
$settings->add(new admin_setting_configexecutable('vidtrans_ffmpeg', get_string('ffmpeg',
                        'vidtrans'),
                get_string('ffmpeghelp', 'vidtrans'), ''));
//OGGZ info for file conversoin.
$settings->add(new admin_setting_configexecutable('vidtrans_oggzinfo', get_string('oggz-info',
                        'vidtrans'),
                get_string('oggz-infohelp', 'vidtrans'), ''));
?>