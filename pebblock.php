<?php

// 0. If you want to pretend that this is secure, then md5 hash a password, and put
//    that value here. Leave it as an empty string ('') for NO PROTECTION WHATSOEVER.
//    If you're on a Mac, you can use 'md5 -s "PASSWORD"' to get a hash.
$password = '';

// 1. Create a new app: https://api.lockitron.com/v1/oauth/applications
//    Put a name, and whatever you want for the Redirect URL (not important)
//    Enter the value from "Your Access Token" (at the bottom of the page once you've saved)
$access_token = '';

// 2. Go to https://lockitron.com/dashboard (log in) and you can see all your locks.
//    List the locks you want to be able to control via Pebblock.
//    key = shortname (use in the URL),
//    val = the lock_id for the lock (the end part of the URL once you click on a lock in your dashboard)
$locks = array(
	'LOCK1' => '12345678-1234-1234-1234-1234567890ab',
	'LOCK2' => '12345678-1234-1234-1234-1234567890ac',
);

// 3. That's it. Now access this script via a browser/HTTP GET request, using something like
//    http://domain.com/path/to/this/pebblock.php?lock=MYLOCK&do=unlock&password=RAWPASSWORD


// This is the request format that we need to use to lock/unlock a Lockitron lock
$lockitron = "https://api.lockitron.com/v1/locks/%1s/%2s";

// If a password is set, then make sure the request includes one, and that it's correct
if ( ! empty( $password ) && ( ! isset( $_GET['password'] ) || md5( $_GET['password'] ) !== $password ) )
	die( 'Invalid password' );

// Must specify a lock, and it must exist
if ( ! isset( $_GET['lock'] ) || ! isset( $locks[ $_GET['lock'] ] ) )
	die( 'Make sure you specify a lock via ?lock=' );

// Need an action to perform, and we only know about lock and unlock
if ( ! isset( $_GET['do'] ) || ! in_array( $_GET['do'], array( 'lock', 'unlock' ) ) )
	die( 'Lock or Unlock? Specify one or the other via ?do=' );

// If an access token is supplied via the request (?access_token=) then use that instead
if ( ! empty( $_GET['access_token'] ) )
	$access_token = $_GET['access_token'];

// Just use cURL to make the request (must be a POST) because it's easy and available everywhere
$ch = curl_init(
	sprintf(
		$lockitron,
		$locks[ $_GET['lock'] ],
		$_GET['do']
	)
);
curl_setopt( $ch, CURLOPT_POST, 1 );
curl_setopt( $ch, CURLOPT_POSTFIELDS, array( 'access_token' => $access_token ) );
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
curl_setopt( $ch, CURLOPT_HEADER, 0 );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

$response = curl_exec( $ch );

if ( 200 == curl_getinfo( $ch, CURLINFO_HTTP_CODE ) )
	die( $_GET['lock'] . ' ' . $_GET['do'] . 'ed'  );
else
	die( 'Failed to ' . $_GET['do'] . ' ' . $_GET['lock'] );
