<?php use App\Util\Util; ?>
<!DOCTYPE html><html xmlns=http://www.w3.org/1999/xhtml><head><meta http-equiv=Content-Type content=text/html; charset=utf-8>
<link href=http://www.400rhett.com/css/jquery-ui.min.css rel=stylesheet>
<?php
 foreach ($styleSheets as $index => $href): ?>
    <link href=<?php echo $href;?> rel=stylesheet>
<?php endforeach; ?>

<body id=schedule-a-tour contact>
<!-- contact -->
<section class=content><div class=container>
<div class=row><div class=col-md-12><div class=page-title><h1>A user has submitted a form for property: <?php echo $apartmentName;?>!</h1><div class=divder-teal></div></div></div></div>
<div class=row><div class=col-md-12>Contact Type: <b><?php echo array_get($contact, 'mode');?></b></div></div>
<div class=row><div class=col-md-12>Contact Time: <b><?php echo gmdate("Y-m-d H:i:s");?> (GMT)</b><br>
<?php
$timezone  = -5; //(GMT -5:00) EST (U.S. & Canada)
echo "<b>" . gmdate("Y/m/j H:i:s", time() + 3600*($timezone+date("I"))) . " (EST)</b><br>";
$timezone = -7;
echo "<b>" . gmdate("Y/m/j H:i:s", time() + 3600*($timezone+date("I"))) . " (MST)</b><br>";
$timezone = -9;
echo "<b>" . gmdate("Y/m/j H:i:s", time() + 3600*($timezone+date("I"))) . " (PST)</b><br>";
?>
<br></div></div>
<div class=row><div class=col-md-6><p>Below is the user's information.
<div class=row><div class=col-md-6><p>The following was submitted:</p><p>
<?php if ($mode == 'briefContact'): ?>
Name: <?php echo $contact['name']; ?><br>

Email: <?php echo $contact['email'];?><br>

Message: <?php echo $contact['message']; ?><br>

Action Requested: SCHEDULE A TOUR (From Front Page )
<?php endif;?>

<?php if ($mode == 'more'): ?>

First Name: <?php echo $contact['fname'];?> <br>

Last Name: <?php echo $contact['lname'];?><br>

Email: <?php echo $contact['email'];?> <br>

Phone: <?php echo $contact['phone'];?> <br>

Move-in date: <?php echo $contact['movein'];?> <br>

<?php if(Util::arrayGet($contact,'limited.unittype')): ?>
<br>
<b>User is interested in this unit:</b><br>
Unit Type: <?php echo Util::arrayGet($contact,'limited.unittype','--no unit type specified--');?><br>

Bed: <?php echo Util::arrayGet($contact,'limited.bed','--not available--');?><br>

Bath: <?php echo Util::arrayGet($contact,'limited.bath','--not available--');?><br>

Square Feet: <?php echo Util::arrayGet($contact,'limited.sqft','--not available--');?><br>
<?php endif;?>

Action Requested: Request For More Information<br>
<?php endif; ?>


<?php if ($mode == 'contact'): ?>

First Name: <?php echo $contact['first_name'];?> <br>

Last Name: <?php echo $contact['last_name'];?><br>

Email: <?php echo $contact['email'];?> <br>

Phone: <?php echo $contact['phone'];?> <br>

Move-in date: <?php echo $contact['movein'];?> <br>

Action Requested: Contact<br>
<?php endif; ?>

<?php if ($mode == 'schedule-a-tour'): ?>
First Name: <?php echo $contact['firstname'];?> <br>

Last Name: <?php echo $contact['lastname'];?><br>

Email: <?php echo $contact['email'];?> <br>

Phone: <?php echo $contact['phone'];?> <br>

Move-in date: <?php echo $contact['moveindate'];?> <br>

Visit date: <?php echo $contact['visitdate'];?> <br>

Visit time: <?php echo $contact['visittime'];?><br>

Action Requested: SCHEDULE A TOUR<br>
<?php endif; ?>

<?php if ($mode == 'apply-online'): ?>
First Name: <?php echo $contact['fname'];?> <br>

Last Name: <?php echo $contact['lname'];?><br>

Email: <?php echo $contact['email'];?> <br>

Phone: <?php echo $contact['phone'];?> <br>

Action Requested: APPLY ONLINE<br>
<?php endif; ?>

<?php if ($mode == 'apply-now' || $mode == 'avail-unit'): ?>
First Name: <?php echo $contact['fname'];?> <br>

Last Name: <?php echo $contact['lname'];?><br>

Email: <?php echo $contact['email'];?> <br>

Phone: <?php echo $contact['phone'];?> <br>

Unit number: <?php echo $contact['unit'];?> <br>

Preferred floor plan: <?php echo $contact['floorplan'];?> <br>

Action Requested: APPLY NOW<br>
<?php endif; ?>


<?php if ($mode == 'pre-applicaton'): ?>
First Name: <?php echo $contact['fname'];?> <br>

Last Name: <?php echo $contact['lname'];?><br>

Email: <?php echo $contact['email'];?> <br>

Phone: <?php echo $contact['phone'];?> <br>

Preferred floor plan: <?php echo $contact['floorplan'];?> <br>

Action Requested: PRE-APPLICATION<br>
<?php endif; ?>

<br><br>
</p></div></div></div></section>
</div></div></body></html>
