<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
#
# Copyright 2004 Dirk Meyer, Im Grund 4, 34317 Habichstwald
#	dirk.meyer@dinoex.sub.org
#
# Updates on:
#	http://iroffer.dinoex.net/
#

#
# Konfigurtaion:
#
# IRC-Nick des Bots
#
# 1) Name des Verzeichnissses ist der Nick
$nick = ereg_replace( '/[^/]*[.]php$', '', $_SERVER[ 'PHP_SELF' ] );
$nick = ereg_replace( '^/(.*/)*', '', $nick );
$nick = 'XDCC|'.$nick;
# 2) Nick wird fest eingetragen:
#$nick = 'XDCC|irofferbot';

# Statusfiles des bots hier angeben
$filenames = array(
	'mybot.state'
);

# COPY+PASTE per Javascript aktiv=1, inaktiv=0
$javascript = 1;

# Pfad zu einer Cache datei, Bitte leere Datei anlegen.
$cache_file = 'size.data';

# Unter welchen Namen solle Packs ohne Gruppe angezeigt werden.
$default_group = '.neu';

$base_path = './';
$chroot_path = '';

# Welche Daten sollen angezeight werden?
$statistik = array (
#	'version' => 'Version',
#	'uptimetext' => 'Online',
#	'timetext' => 'letztes Update',
	'freeslots' => 'Freie Slots',
#	'maxslots' => 'Anzahl Slots',
	'queue' => 'Warteschlange',
#	'minspeed' => 'Mindest-Rate',
#	'maxspeed' => 'Maximale-Rate',
	'current' => 'Aktuelle Bandbreite',
#	'cap' => 'Maximale Bandbreite',
#	'record' => 'Rekord-Rate',
#	'send' => 'Rekord-Download',
#	'dailytext' => "Traffic heute",
#	'weeklytext' => "Traffic diese Woche",
#	'monthlytext' => "Traffic diesem Monat",
);

# Ende der Einstellungen

include 'iroffer-state.php';

?>
<html>
<head>
<?php echo $meta_generator; ?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="content-language" content="de-de">
<link rel="icon" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="iroffer-state.css">
<title><?php echo $nick; ?></title>
<?php echo $javascript_code; ?>
</head>
<body>
<center>
<?php

$bot = new botlist();
$bot->nick = $nick;

$bot->filenames = $filenames;
$bot->base_path = $base_path;
$bot->chroot_path = $chroot_path;
$bot->statistik = $statistik;
# $bot->add_url = 'debug=1';

$bot->read_state();
$bot->write_table();

?>
</center>
</body>
</html>
