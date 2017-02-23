<!DOCTYPE html>
<html>
<head>
<meta charset=utf-8>
<title>London Midland Watch</title>
<meta name="viewport" content="width=device-width">
<link rel="stylesheet" href="/shared/style.css">
<link rel="stylesheet" href="/shared/project.css">
<link rel="stylesheet" href="/shared/mediaqueries.css">
<script type="text/javascript" src="/shared/sjgscripts.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js"></script>
<style>
	.chart-legend li span {
    display: inline-block;
    width: 12px;
    height: 12px;
    margin-right: 5px;
/*		list-style-type: none !important; make this work in due course */
}
</style>
</head>
<body>
<div id="main">
<h1>London Midland Watch</h1>
	<p>
		Documenting one individual's experience of the service delivered on the London Midland Birmingham Cross City line train.
	</p>
	<div id="add-train" class="half-left">
		<form action="/" method="post">
<!--		<label for="TwitterName">Your name:</label> -->
		<input type="hidden" name="TwitterName" id="TwitterName" value="simon gray"/><br />
		<label for="Station">Station:</label>
<!-- 		<input type="text" name="Station" id="Station" required /><br /> -->
		<select name="Station" id="Station">
			<option value="Bournville">Bournville</option>
			<option value="Five Ways">Five Ways</option>
			<option value="Birmingham New Street">Birmingham New Street</option>
			<option value="Selly Oak">Selly Oak</option>
		</select><br />
		<label for="TimeDue">Time due:</label>
		<input type="time" name="TimeDue" id="TimeDue" value="<?php echo date("H:i"); ?>" required /><br />
		<label for="TimeArrived">Time left:</label>
		<input type="time" name="TimeArrived" id="TimeArrived" value="<?php echo date("H:i"); ?>" required /><br />
		<label for="TheDate">Date:</label>
		<input type="date" name="TheDate" id="TheDate" value="<?php echo date("Y-m-d"); ?>" /><br />
		<input type="hidden" name="timeStamp" value="<?php echo time(); ?>" />
		<input type="submit" value="Add journey">
		</form>
	</div>
</div>
<div id="colophon">
<p>
Brought to you by <a href="http://about.me/simon.gray" title="About me - simon gray">simon gray</a>. If you like this, it'd be nice if you could have a listen to <a href="http://www.winterval.org.uk/" title="The Winterval Conspiracy">some of my music</a>.
</p>
</div>
</body>
</html>