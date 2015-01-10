<?php
include_once '../sys/core/init.inc.php';

$cal = new Calendar($dbo,"2015-01-01 12:00:00");

$page_title = "Events Calendar";
$css_files = array('style.css');
/*
 * Include the header
 */
include_once 'assets/common/header.inc.php';
?>
<div id="content">
<?php

echo $cal->buildCalendar();
?>
</div><!-- end #content -->
<?php
/*
 * Include the footer
 */
include_once 'assets/common/footer.inc.php';
