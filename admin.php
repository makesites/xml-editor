<?php
/* GENERAL CONFIGURATION */
$XMLfile = $_SERVER['DOCUMENT_ROOT'] . '/scripts/news.xml';	// the path for the generated XML file

/* DATEBASE CONFIGURATION */
$dbHost = "localhost";                              				// Database host
$dbName = "database_name";                          				// Database name
$dbUser = "database_user";                              			// Database user
$dbPasswd = "database_password";                            					// Database password
$dbTable = "news_xml";  											// The name of the table that contains our data

/* ADMIN CONFIGURATION */
$ADMIN_NAME = "<ENTER DESIRED USER>";                      						// Admin's login ID
$ADMIN_PASS = "<ENTER DESIRED PASS>";                      						// Admin's password

/* PAGE CONFIGURATION */
$PAGE_TITLE = "XML NEWS";                							// Page's Header title
$PAGE_HEADER_COLOR = "#DEDFDE";                						// Page's Header background color


$adminName = $_REQUEST['adminName'];
$adminPasswd = $_REQUEST['adminPasswd'];

/* THE CONDITIONS THAT REDIRECT THE USER */
if( verifyAdmin() )
{
	// logged in - continue....
	dbConnect();
	if( isset( $_REQUEST['userAction'] )  && $_REQUEST['userAction'] == "logout")
	{
		// fuck it there's nothing to do here.
		logoutAdmin();
	}
	elseif ( isset( $_REQUEST['userAction'] ) && $_REQUEST['userAction'] == "add" )
	{
		addNews();
	}
	elseif ( isset( $_REQUEST['userAction'] ) && $_REQUEST['userAction'] == "delete" )
	{
		deleteNews();
	}
	else
	{
		// things are going well - see what we need to do...
		viewRecords();
	}
}
else
{
	// not logged in - what to do...
	if( isset( $_REQUEST['userAction'] ) && $_REQUEST['userAction'] == "login" )
	{
		loginAdmin();
	}
	else
	{
		viewLogin();
	}
}



/* CORE FUNCTIONS */
function viewRecords()
{
	global $dbTable;
	showHeader("View News");
	showNew();
	echo '<h2>Archive</h2>' . "\n";
	$sql = 'SELECT * FROM ' . $dbTable . ' ORDER BY id DESC';
    $result = mysql_query($sql);
	echo '  <table border="1" cellpadding="4" cellspacing="4" align="center">' . "\n";
	while( $row = mysql_fetch_array( $result ) )
	{
		echo '    <tr>' . "\n";
		echo '      <td align="center">' . $row['id'] . '</td>' . "\n";
		echo '      <td>' . $row['title'] . '</a></td>' . "\n";
		echo '      <td>' . $row['link'] . '</td>' . "\n";
		echo '      <td>' . $row['description'] . '</td>' . "\n";
		echo '      <td>' . $row['pubDate'] . '</td>' . "\n";
		echo '      <td><a href="' . $_SERVER['PHP_SELF'] . '?userAction=delete&id=' . $row['id'] . '"> X </a></td>' . "\n";
		echo '    </tr>' . "\n";
	}
	echo '  </table>' . "\n";
	showFooter();
}

function showNew()
{
	echo '<h2>Add News</h2>' . "\n";
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n";
	echo '<input type="hidden" name ="userAction" value="add" />' . "\n";
	echo '<div id="fields">' . "\n";
	echo '<label for="title">Title</label>' . "\n";
	echo '<input type="text" name="title" id="title" size="30" />' . "\n";
	echo '<br />' . "\n";
	echo '<label for="link">Link</label>' . "\n";
	echo '<input type="text" name="link" id="link" size="30" />' . "\n";
	echo '<br />' . "\n";
	echo '<label for="description">Description</label>' . "\n";
	echo '<textarea name="description" id="description" rows="2" cols="22"></textarea>' . "\n";
	echo '<br />' . "\n";
	echo '<label for="pubDate">Published</label>' . "\n";
	echo '<input type="text" name="pubDate" id="pubDate" size="30" />' . "\n";
	echo '<br />' . "\n";
	echo '</div>' . "\n";
	echo '<br />' . "\n";
	echo '<p align="center"><input type="submit" value="  ADD  " /></p>' . "\n";
}

function addNews()
{
	global $dbTable;
	// get published date - format: Mon, 01 Jan 2000 00:00:00 CET
	($_REQUEST['pubDate'] != "")
	? $pubDate = $_REQUEST['pubDate']
	: $pubDate = date("D, d M Y h:i:s") . ' CET';

	$sql = "INSERT INTO " . $dbTable . " ( title, link, description, pubDate ) VALUES ( '" . addslashes ( $_REQUEST['title'] ) . "', '" . addslashes ( $_REQUEST['link'] ) . "', '" . addslashes ( $_REQUEST['description'] ) . "', '" . addslashes ( $pubDate ) . "' )";
	$result = mysql_query($sql) or error( mysql_error() );
	writeXML();
	header( "Location: " . $_SERVER['PHP_SELF'] );
}

function deleteNews()
{
	global $dbTable;
	$sql = "DELETE  FROM " . $dbTable . " WHERE id=" . $_REQUEST['id'] . "";
	$result = mysql_query($sql) or error( mysql_error() );
	writeXML();
	header( "Location: " . $_SERVER['PHP_SELF'] );
}


function writeXML()
{
	global $dbTable, $XMLfile;
	$sql = 'SELECT * FROM ' . $dbTable . ' ORDER BY id DESC LIMIT 5';
    $result = mysql_query($sql);
	$output = '<?xml version="1.0"?>' . "\n";
	$output .= '<rss version="2.0">' . "\n";
	$output .= '	<channel>' . "\n";
	$output .= '		<title>KDI Network News</title>' . "\n";
	$output .= '		<link>http://feeds.feedburner.com/kdi</link>' . "\n";
	$output .= '		<description>Small news from the friendly network...</description>' . "\n";
	$output .= '		<language>en-us</language> . "\n"';
	$output .= '		<copyright>© ' . date("Y") . ' KDIweb.net</copyright>' . "\n";
	$output .= '		<pubDate>' . date("D, d M Y h:i:s") . ' CET</pubDate>' . "\n";
	$output .= '		<ttl>5</ttl>' . "\n";
	$output .= '		<image>' . "\n";
	$output .= '			<title>KDI Network News</title>' . "\n";
	$output .= '			<link>http://www.kdiweb.net/scripts/news.xml</link>' . "\n";
	$output .= '			<url>http://www.kdiweb.net/images/kdirss.gif</url>' . "\n";
	$output .= '			<width>144</width>' . "\n";
	$output .= '			<height>33</height>' . "\n";
	$output .= '			<description>Alternative Software Company and Online Services.</description>' . "\n";
	$output .= '		</image>' . "\n";
	while( $row = mysql_fetch_array( $result ) )
	{
		$output .= '		<item>' . "\n";
		$output .= '			<title>' . $row['title'] . '</title>' . "\n";
		$output .= '			<link>' . $row['link'] . '</link>' . "\n";
		$output .= '			<description>' . $row['description'] . '</description>' . "\n";
		$output .= '			<pubDate>' . $row['pubDate'] . '</pubDate>' . "\n";
		$output .= '		</item>' . "\n";
	}
	$output .= '	</channel>' . "\n";
	$output .= '</rss>' . "\n";

	$container = fopen($XMLfile, 'w');
	fwrite($container, $output);
	fclose($container);
}

/* BASIC FUNCTIONS */
function dbConnect()
{
	global $dbHost, $dbUser, $dbPasswd, $dbName;
	mysql_connect( $dbHost, $dbUser, $dbPasswd ) or error( mysql_error() );
	mysql_select_db( $dbName );
}

function verifyAdmin()
{
	global $ADMIN_NAME, $ADMIN_PASS, $adminPasswd, $adminName;
	session_start();
	if( session_is_registered( "adminName" ) && session_is_registered( "adminPasswd" ) )
	{
		if( $adminName == $ADMIN_NAME && $adminPasswd == $ADMIN_PASS )
			return true;
	}
	return false;
}

function loginAdmin() {
	global $ADMIN_NAME, $ADMIN_PASS, $adminPasswd, $adminName;
	$adminName = trim( $adminName );
	$adminPasswd = trim( $adminPasswd );
	if( $adminName == "" ) error( "Admin name required" );
	if( $adminPasswd == "" ) error( "Admin password required" );
	if( $adminName != $ADMIN_NAME ) error( "Invalid admin name" );
	if( $adminPasswd != $ADMIN_PASS ) error( "Invalid password" );
	session_start();
	session_register( "adminName" );
	session_register( "adminPasswd" );
	header( "Location: ./admin.php" );
}

function logoutAdmin()
{
	session_start();
	session_unregister( "adminName" );
	session_unregister( "adminPasswd" );
	header( "Location: ./admin.php" );
}

function viewLogin()
{
	showHeader("Login");
	echo '<h2>Admin Login</h2>' . "\n";
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n";
	echo '<input type="hidden" name ="userAction" value="login" />' . "\n";
	echo '<div id="fields">' . "\n";
	echo '<label for="adminName">Name</label>' . "\n";
	echo '<input type="text" name="adminName" id="adminName" size="20" maxlength="50" />' . "\n";
	echo '<br />' . "\n";
	echo '<label for="adminPasswd">Password</label>' . "\n";
	echo '<input type="password" name="adminPasswd" id="adminPasswd" size="20" maxlength="12" />' . "\n";
	echo '<br />' . "\n";
	echo '</div>' . "\n";
	echo '<p align="center"><input type="submit" value="  Login  " /></p>' . "\n";
	echo '</form>' . "\n";
	showFooter();
}

function showHeader($title)
{
	global $PAGE_TITLE;
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"' . "\n";
	echo '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
	echo '<html><head>';
	echo '<title>' . $PAGE_TITLE . ': ' . $title . '</title>';
	echo '<style type="text/css">' . "\n";
	echo 'body {';
	echo ' font: normal 12px Arial, Verdana, Tahoma; ';
	echo '}' . "\n";
	echo 'h2 {';
	echo ' text-align: center; ';
	echo '}' . "\n";
	echo '#fields {';
	echo ' width: 400px; margin: 0 auto; ';
	echo '}' . "\n";
	echo '#fields label {';
	echo ' display: block; width: 120px; float: left; text-align: right; margin: 7px 8px; ';
	echo '}' . "\n";
	echo '#fields input {';
	echo ' float: left; margin: 4px 0;';
	echo '#fields textarea {';
	echo ' float: left; margin: 4px 0;';
	echo '}' . "\n";
	echo '#fields br {';
	echo ' clear: both; ';
	echo '}' . "\n";
	echo '</style>';
	echo '</head><body>';
}

function showFooter()
{
	echo '</body></html>';
}

function error( $output )
{
	showHeader('');
	echo $output;
	showFooter();
	exit;
}

?>