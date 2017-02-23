<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$ServerPath = $_SERVER['DOCUMENT_ROOT'];
$ServerPath .= "/shared/functions.php";
include_once($ServerPath);

$AverageOnTrain = 150;
$MinimumWage = 7.2;
$MedianWage = 13.2;
$MyWage = 17.70;

$TwitterName = sanitise(mysqli_real_escape_string($connect, $_REQUEST["TwitterName"]));
$Station = sanitise(mysqli_real_escape_string($connect, $_REQUEST["Station"]));
$TimeDue = sanitise(mysqli_real_escape_string($connect, $_REQUEST["TimeDue"]));
$TimeArrived = sanitise(mysqli_real_escape_string($connect, $_REQUEST["TimeArrived"]));
$TheDate = sanitise(mysqli_real_escape_string($connect, $_REQUEST["TheDate"]));

$timeStamp = mysqli_real_escape_string($connect, $_REQUEST['timeStamp']);
$timeNow = time();
if(($timeNow - $timeStamp) > 2) { $timeOK = 1; } else { $timeOK = 0; } // if the form is filled in in under two seconds suspect botspam

if ($Station && $timeOK == 1)
{
	$addDataSQL = $connect->prepare("INSERT INTO Trains (TwitterName,
	Station,
	TimeDue,
	TimeArrived,
	TheDate) VALUES (?, ?, ?, ?, ?)");
	$addDataSQL->bind_param("sssss",
	$TwitterName,
	$Station,
	$TimeDue,
	$TimeArrived,
	$TheDate);
	if(!$addDataSQL->execute()){trigger_error("There was an error:" . $connect->error, E_USER_WARNING);}
	$addDataSQL->close();
	
	$Ack = "<p><strong>Journey added!</strong></p>";
}

$page = explode("/",$_SERVER['REQUEST_URI']);
$flag = mysqli_real_escape_string($connect, $page[1]);

$trainlist = "";
	$fromDate = date('Y-m-d', strtotime('-30 days'));
		if($flag == 'week') { $fromDate = date('Y-m-d', strtotime('-7 days')); }
		if($flag == 'year') { $fromDate = date('Y-m-d', strtotime('-365 days')); }
		if($flag == 'leaffall2015') { $fromDate = "2015-10-18' AND TheDate < '2015-12-13"; }
		if($flag == 'leaffall2016') { $fromDate = "2016-10-23' AND TheDate < '2016-12-11"; }
	$sql = "SELECT * FROM Trains WHERE TheDate > '" . $fromDate . "' ORDER BY TheDate DESC, TimeDue DESC, TrainID DESC";
	$stmt = $connect->prepare($sql);
	$stmt->execute();
	$stmt->store_result();
	$results = array();
	bind_all($stmt, $results);

	if(substr($flag, 0, 4) == "leaf") { $fromDate = substr($fromDate, 0, 10); }

	$numtrains = 0;
	$minslate = 0;
	$numontime = 0;
	$numLateDfT = 0;
	$numlate = 0;
	$num3minslate = 0;
	$num5minslate = 0;
	$num10minslate = 0;

	while($stmt->fetch())
	{
		$TimeArrived = explode(":",$results['TimeArrived']);
		$TimeDue = explode(":",$results['TimeDue']);
		$lateness = ($TimeArrived[0] * 60 + $TimeArrived[1]) - ($TimeDue[0] *60 + $TimeDue[1]); 
		
		$numtrains = $numtrains +1;
		$minslate += $lateness;
		$avelate = $minslate / $numtrains;
		if($lateness < 3) { $numontime = $numontime + 1; } else { $numlate = $numlate + 1; }
		if($lateness > 4) { $numLateDfT = $numLateDfT + 1; }
		if($lateness > 2 && $lateness < 5) { $num3minslate = $num3minslate + 1; }
		if($lateness > 4 && $lateness < 10) { $num5minslate = $num5minslate + 1; }
		if($lateness > 9) { $num10minslate = $num10minslate + 1; }
		
		if($lateness > 2)
		{
			$trainlist .= "<div class=\"late\" id=\"train-" . $results['TrainID'] . "\">\r\n";
		}
		else
		{
			$trainlist .= "<div class=\"ontime\" id=\"train-" . $results['TrainID'] . "\">\r\n";
		}
		$trainlist .= $TimeDue[0] . ":" . $TimeDue[1] . " at <strong>" . unescape($results['Station']) . "</strong> on " . $results['TheDate'] . "<br />\r\n\r\n";
		$trainlist .= "Departed at " . $TimeArrived[0] .":" . $TimeArrived[1] . ", <em>" . $lateness . "</em> minutes late.\r\n\r\n";
//		$trainlist .= "<em>" . unescape($results['TwitterName']) . "</em></p>\r\n\r\n";
		$trainlist .= "</div>\r\n\r\n";
	}
	$stmt->close();

?>
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

td {
	text-align: center; vertical-align: middle;
}
</style>
</head>
<body>
<div id="main">
<h1>London Midland Watch</h1>
<?php
	if($Ack) { echo $Ack; }
?>
	<p>
		Documenting one individual's experience of the service delivered on the London Midland Birmingham Cross City line train, mostly travelling during morning and evening commuter times.
	</p>
<div id="statstables">
<table style="width:100%;">
	<caption><?php echo "<strong>Journeys since " . $fromDate . "<br />Average <strong>" . number_format($avelate,2) . "</strong> minutes delay, <strong>" . $minslate . "</strong> total minutes late."; ?></caption>
	<tr><th rowspan="2"></th><th rowspan="2" scope="col">Total</th><th rowspan="2" scope="col">On time</th><th colspan="3" scope="col">Late</th></tr>
	<tr><th scope="col">3-5 minutes late</th><th scope="col">5-10 minutes late</th><th scope="col">over 10 minutes late</th></tr>
	<tr><th rowspan="3" scope="row">Total</th><td rowspan="3"><?=$numtrains; ?></td><td rowspan="3"><?=$numontime; ?></td><td rowspan="2"><?php echo $num3minslate; ?></td><td><?php echo $num5minslate; ?></td><td><?php echo $num10minslate; ?></td></tr>
	<tr><td colspan="2"><?=$numLateDfT; ?></td></tr>
	<tr><td colspan="3"><?=$numlate; ?></td></tr>
</table>
	<p><em>(Department for Transport counts journeys as late after 5 minutes, I count journeys as late after 3 minutes)</em></p>

<table style="width:100%;">
	<caption>Economic cost to passengers of delays based on average train occupancy of <strong><?=$AverageOnTrain; ?></strong> passengers</caption>
	<tr><th scope="row">Passenger salary</th><th scope="col">£<?=number_format($MinimumWage,2); ?>/hr<br /><em>(Minimum wage)</em></th><th scope="col">£<?=number_format($MedianWage,2); ?>/hr<br /><em>(Median wage)</em></th><th scope="col">£<?=number_format($MyWage,2); ?>/hr<br /><em>(My wage)</em></th></tr>
	<tr><th scope="row">Per passenger</th><td>£<?=number_format($minslate * ($MinimumWage / 60),2); ?></td><td>£<?=number_format($minslate * ($MedianWage / 60),2); ?></td><td>£<?=number_format($minslate * ($MyWage / 60),2); ?></td></tr>
	<tr><th scope="row">Per train</th><td>£<?=number_format(($minslate * ($MinimumWage / 60)) * $AverageOnTrain,2); ?></td><td>£<?=number_format(($minslate * ($MedianWage / 60)) * $AverageOnTrain,2); ?></td><td>£<?=number_format(($minslate * ($MyWage / 60)) * $AverageOnTrain,2); ?></td></tr>
</table>
<br />
</div>
	<div id="charts"><div id="js-legend" class="chart-legend" style="float: left;margin-right: 10px;"></div>
     <canvas id="alltrains" width="200" height="200"/>
		
<script>

    var alltrains = [
            {
                value: <?php echo $numontime; ?>,
                color:"#139831",
                highlight: "#FF5A5E",
                label: "<?php echo number_format(($numontime / $numtrains) * 100, 1); ?>% on time"
            },
            {
                value: <?php echo $num3minslate; ?>,
                color: "#A1F324",
                highlight: "#5AD3D1",
                label: "<?php echo number_format(($num3minslate / $numtrains) * 100, 1); ?>% 3-5 minutes late"
            },
            {
                value: <?php echo $num5minslate; ?>,
                color: "#F3BC1C",
                highlight: "#FFC870",
                label: "<?php echo number_format(($num5minslate / $numtrains) * 100, 1); ?>% 5-10 minutes late"
            },
            {
                value: <?php echo $num10minslate; ?>,
                color: "#F3321C",
                highlight: "#A8B3C5",
                label: "<?php echo number_format(($num10minslate / $numtrains) * 100, 1); ?>% over 10 minutes late"
            },
         ];

        window.onload = function(){
            var ctx = document.getElementById("alltrains").getContext("2d");
            var alltrainsDoughnut = new Chart(ctx).Doughnut(alltrains);
document.getElementById('js-legend').innerHTML = alltrainsDoughnut.generateLegend();
        };



</script>

	</div>
	<div id="londonmidland">
		<p>London Midland <a href="https://www.londonmidland.com/about-us/company-information/train-performance" title="London Midland performance">claimed punctuality: <strong>73%</strong></a> trains within three minutes; actual experienced punctuality here: <strong><?php echo number_format((($numontime + $num3minslate) / $numtrains) * 100,1) ?>%</strong>.</p>
		<p>
			Show last <a href="/week" title="Last seven days">seven days</a>, last <a href="/" title="Last 30 days">30 days</a>, last <a href="/year" title="One year">year</a>, <a href="/leaffall2015" title="leaf fall 2015">leaf fall 2015</a>, <a href="/leaffall2016" title="leaf fall 2016">leaf fall 2016</a>.
		</p>
		<?php
/* 		$html = file_get_contents("your url"); // this came from http://stackoverflow.com/questions/24128832/php-scraping-text-from-a-specific-class-on-another-website - it might be useful elsewhere
$DOM = new DOMDocument();
$DOM->loadHTML($html);
$finder = new DomXPath($DOM);
$classname = 'nickname';
$nodes = $finder->query("//*[contains(@class, '$classname')]");
foreach ($nodes as $node) {
  echo $node->nodeValue;
}  */
		?>
	</div>
  <div id="content">
	<?php
	echo $trainlist;
	?>
  </div>
<!-- <p>
	The great autumn railway Leaves on the Line event is here again, and for the second year in a row <a href="http://www.londonmidland.com" title="London Midland">London Midland</a> have implemented a leaf-fall timetable to accommodate it. 
	</p>
	<p>
		Any reasonable person accepts the need for train companies to operate leaf-fall timetables, and although 30 years ago 'the wrong type of leaves' was the annual tabloid newspaper joke, nowadays the <a href="https://en.wikipedia.org/wiki/Slippery_rail" title="Slippery rail on Wikipedia">problems leaves on the line cause</a> for modern trains requiring them to slow down are generally understood. Given the need for trains to slow down, you'ld think a leaf-fall timetable would simply slow the trains down - for example, reducing a 10 minute frequency to, say, 15 minutes.
	</p>
	<p>
		London Midland in Birmingham, however, have adopted an alternative strategy; instead of slowing the trains down to give passengers a longer - but predictable - journey time, they've instead kept the notional 10 minute / six trains an hour frequency for the inner core stations, but two trains an hour being expressed from half way along missing out the remaining stations until the end of that train's line - the excuse given being that these trains will be able to catch up any lost time to get the timetable back on track quickly.
	</p>
	<p>
		At first reading that seems fair and reasonable, but deeper thinking reveals that this works well for London Midland, but not so well for the actual passengers - certain trains stopping at fewer stops and being able to more quickly get back in sync will by mathematical definition result in the lateness statistics showing fewer delayed trains. And delay statistics of themselves present a misleading view of actual passenger experience anyway - if the statistics are the average of the whole day's trains, yet (for example) 80% of journeys take place during two particular time windows, and the delayed trains are at best spread evenly through the day or worse, mostly occurring during the peak travelling times, then the published statistics will show better performance than the actual passenger experience.
	</p>
	<p>
		And worst of all, the timetable as implemented actually punishes the people who are most likely to need to use the train the worst - for people living within the inner core who still get a full service, they have an easier choice to switch to the bus or even walk. The people living in the outer zone are the ones who are experiencing the reduced service, and for the people living in the outer zone switching to the bus is a less viable option.
	</p>
	<p>
		A leaffall timetable which is fairer <strong>could</strong> be devised, though. One option could have been - since the point of the exercise is the necessity to slow the trains down - simply to have slowed all the trains down on the timetable by reducing the frequency from every 10 minutes to every 15 minutes. Or if that's too complicated to arrange around the rest of the network, having the leaffall trains skipping the inner stations rather than the outer stations would lessen the impact on the worst affected passengers. Or at the very least, removing only one rather than two trains an hour from any given station and removing them from alternating stations instead during that hour would be better for passengers than the arrangement as adopted!
	</p> -->
	</div>
<div id="colophon">
<p>
Brought to you by <a href="http://about.me/simon.gray" title="About me - simon gray">simon gray</a>. If you like this, it'd be nice if you could have a listen to <a href="http://www.winterval.org.uk/" title="The Winterval Conspiracy">some of my music</a>.
</p>
</div>
</body>
</html>