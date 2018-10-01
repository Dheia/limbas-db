<?php
/*
 * Copyright notice
 * (c) 1998-2018 Limbas GmbH(support@limbas.org)
 * All rights reserved
 * This script is part of the LIMBAS project. The LIMBAS project is free software; you can redistribute it and/or modify it on 2 Ways:
 * Under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 * Or
 * In a Propritary Software Licence http://limbas.org
 * The GNU General Public License can be found at http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license from the author is found in LICENSE.txt distributed with these scripts.
 * This script is distributed WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 * Version 3.5
 */

/*
 * ID:
 */
//	header("Content-type: text/html; charset=UTF-8");
require_once("../classes/php/olTemplate.class.php");
require_once("../classes/php/olUpload.class.php");

$up = new olUpload(dirname($_SERVER['SCRIPT_FILENAME'])."/files", "../templates");

if (isset($_POST['fname'])){
	echo "newname=".$up->beautify_filename($_POST['fname']);
	exit(0);
} else if (isset($_FILES['Filedata'])){
	if ($up->handle())
		echo "OK";
	exit(0);
} 

$html_head = $up->html_header_code();
$content = $up->html_code();

echo <<<EOD
<body>
<head>
{$html_head}
<link href="../styles/olImap.css" rel="stylesheet" type="text/css">
<link href="../styles/olPopup.css" rel="stylesheet" type="text/css">
<style type="text/css">
body{
	background-color:#c0c0c0;
	overflow:hidden;
}</style>
</head>
<body>
{$content}
<div id="DEBUG"></div>
</body>
</html>
EOD;
exit(0);
?>
