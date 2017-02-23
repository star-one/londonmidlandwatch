<header>
<div id="masthead">
<h1><?php echo $SiteName; ?><span style="font-size: 0.6em;"><?php echo " - " . $SiteSubTitle; ?></span></h1>
</div>
<nav id="topmenu">
<ul id="main-nav">
<li><a href="/" title="Home">Home</a></li>
<li><a href="/groups" title="Groups">Groups</a></li>
<li><a href="/members" title="Members">Members</a></li>
<?php if ($_SESSION['LoggedIn']) { ?>
<li><a href="/dashboard" title="Groups">My dashboard</a></li>
<li><a href="/change-password" title="Change password">Change password</a></li> <?php // make that link to be the edit-profile when you've done that page ?>
<li><a href="/logout" title="Logout">Logout</a></li>
<?php } else { ?>
<li><a href="/login" title="Login / register">Login / register</a></li>
<?php
}
?>
</ul>
</nav>
</header>
<div id="main">