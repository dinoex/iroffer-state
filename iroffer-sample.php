<?php
#
# Copyright 2004-2018 Dirk Meyer, Im Grund 4, 34317 Habichstwald
#	dirk.meyer@dinoex.sub.org
#
# Updates on:
#	http://iroffer.dinoex.net/
#
ob_start('ob_gzhandler');

#
# Configuration:
#
# IRC-Nick of the bot
#
# 1) Get nickname from the directory we are in
$nick = dirname( $_SERVER[ 'PHP_SELF' ] );
$nick = basename( $nick );
$nick = 'XDCC|'.$nick;
# 2) Set nickname by hand
#$nick = 'XDCC|irofferbot';

# Put Statefiles of the bot here.
$filenames = array(
	'mybot.state'
);

# COPY+PASTE with Javascript enabled=1, disabled=0
$javascript = 1;

# limit the length of a filename to n chars, 0=no limit
$max_filename_len = 0;

# Path to a cachefile, please create an empty file.
$cache_file = 'size.data';

# Define a group name for all files without a group.
$default_group = '.neu';

# If set to 1, hide all locked packs
$hide_locked = 0;

# List of groups that are hidden on the web. Use '|' as delimiter.
$hide_groups = '';

# Pathname to "files_dir" in the iroffer config.
$base_path = './';

# Pathname to the chroot directory of the bot.
$chroot_path = '';

# Select which information is shown:
$statistik = array (
#	'uptimetext' => 'Online',
	'timetext' => 'letztes Update',
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
#	'downltext' => "Traffic insgesammt",
);

# Charset of all filenames
$iroffer_charset = 'iso-8859-1';
#$iroffer_charset = 'utf-8';

# End of Configuration

include 'iroffer-state.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php echo $meta_generator; ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $iroffer_charset; ?>">
<meta http-equiv="content-language" content="en-en">
<link rel="icon" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="iroffer-state.css">
<title><?php echo $nick; ?></title>
<?php echo $javascript_code; ?>
</head>
<body>
<center>
<?php

$bot = new iroffer_botlist();
$bot->nick = $nick;

$bot->filenames = $filenames;
$bot->base_path = $base_path;
$bot->chroot_path = $chroot_path;
$bot->statistik = $statistik;
$bot->hide_locked = $hide_locked;
$bot->hide_groups = $hide_groups;
# $bot->add_url = 'debug=1';

$bot->read_state();
$bot->write_table();

?>
</center>
</body>
</html>

