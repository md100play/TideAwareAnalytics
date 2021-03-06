<!-- Author: Michael Dombrowski
Website: MikeDombrowski.com
Github: github.com/md100play/TideAwareAnalytics/
-->
<?php
	$database = "$$$$$$$$$$$$$$$$$$";
	$user = "*****************";
	$pass = "####################";
	$link = mysqli_connect("localhost", $user, $pass, $database) or die("Error " . mysqli_error($link));
	date_default_timezone_set('UTC');
	
	$startDate = date("U", strtotime("-1 week", time()));
	$endDate = date("U", time());
	
	if (isset($_GET['start']) || isset($_GET['end'])){
		if(isset($_GET['start']) && strtotime($_GET['start'])>0){
			$startDate = strtotime($_GET['start']);
			if(!isset($_GET['end']) || !strtotime($_GET['end'])>0){
				$endDate = date("U", strtotime("+1 week", $startDate));
			}
		}
		if(isset($_GET['end']) && strtotime($_GET['end'])>0){
			$endDate = strtotime($_GET['end']);
			if(!isset($_GET['start']) || !strtotime($_GET['start'])>0){
				$startDate = date("U", strtotime("-1 week", $endDate));
			}
		}
	}

	$tz = 0;
	if(isset($_COOKIE['tz'])){
		$tz = $_COOKIE['tz'];
		$startDate = $startDate+$tz;
		$endDate = $endDate+$tz;
	}
	
	function generateChart($result){
		$final = array();
		$dat = array();
		while($row = mysqli_fetch_array($result)){
			if(isset($row['US Versions'])){
				$us = json_decode($row['US Versions'], True);
				foreach($us as $a){
					if(isset($dat[$a['label']])){
						$dat[$a['label']] = ["ver"=>1, "count"=>$dat[$a['label']]["count"]+1];
					}
					else{
						$dat[$a['label']] = ["ver"=>1, "count"=>$a['num']];
					}
				}
			}
			if(isset($row['Non US Versions'])){
				$nus = json_decode($row['Non US Versions'], True);
				foreach($nus as $a){
					if(isset($dat[$a['label']])){
						$dat[$a['label']] = ["ver"=>0, "count"=>$dat[$a['label']]["count"]+1];
					}
					else{
						$dat[$a['label']] = ["ver"=>0, "count"=>$a['num']];
					}
				}
			}			
		}
		foreach($dat as $k=>$v){
			$ver = $v["ver"];
			if($ver == 1){
				$ver = "US Version";
			}
			else{
				$ver = "Non-US Version";
			}
			$tmp["label"] = $ver." ".$k;
			$tmp["data"] = $v["count"];
			array_push($final, $tmp);
		}
		return $final;
	}

	function generateChart2($result){
		$final = array();
		$dat = array();
		while($row = mysqli_fetch_array($result)){
			if(isset($row['US Versions'])){
				$us = json_decode($row['US Versions'], True);
				foreach($us as $a){
					if(isset($dat[$a['label']])){
						$dat[$a['label']] = ["ver"=>1, "count"=>$dat[$a['label']]["count"]+1];
					}
					else{
						$dat[$a['label']] = ["ver"=>1, "count"=>$a['num']];
					}
				}
			}
			if(isset($row['Non US Versions'])){
				$nus = json_decode($row['Non US Versions'], True);
				foreach($nus as $a){
					if(isset($dat[$a['label']])){
						$dat[$a['label']] = ["ver"=>0, "count"=>$dat[$a['label']]["count"]+1];
					}
					else{
						$dat[$a['label']] = ["ver"=>0, "count"=>$a['num']];
					}
				}
			}			
		}
		foreach($dat as $k=>$v){
			$ver = $v["ver"];
			if($ver == 1){
				$ver = "US Version";
			}
			else{
				$ver = "Non-US Version";
			}
			$found = false;
			for($i=0; $i< count($final); $i=$i+1){
				if($final[$i]['label'] == $ver){
					$final[$i]['data'] = $final[$i]['data']+$v['count'];
					$found = true;
				}
			}
			if($found==false){
				$tmp["label"] = $ver;
				$tmp["data"] = $v["count"];
				array_push($final, $tmp);
			}
		}
		return $final;
	}
	
	function compareUsers($currentResult, $oldResult, $start, $end){
		$current = 0;
		$old = 0;
		$currentTotal = 0;
		$oldTotal = 0;
		
		while($row = mysqli_fetch_assoc($currentResult)){
			$rows = json_decode($row['Lookup'], True);
			$found = false;
			foreach($rows as $k=>$v){
				foreach($v as $s){
					if($s >= date("U", strtotime("today", $start)) && $s <= $end){
						if(!$found){
							$current = $current + 1;
							$found = true;
						}
						$currentTotal = $currentTotal + 1;
					}
				}
			}
		}

		while($row = mysqli_fetch_array($oldResult)){
			$rows = json_decode($row['Lookup'], True);
			$found = false;
			foreach($rows as $k=>$v){
				foreach($v as $s){
					if($s >= date("U", strtotime("today", $start-($end-$start))) && $s <= $end-($end-$start)){
						if(!$found){
							$old = $old + 1;
							$found = true;
						}
						$oldTotal = $oldTotal + 1;
					}
				}
			}
		}
		
		return [$current, $old, $currentTotal, $oldTotal];
	}
	
	function dailyUniques($usResult, $nusResult){
		$usTotal = array();
		$nusTotal = array();
		
		while($row = mysqli_fetch_assoc($usResult)){
			$rows = json_decode($row['Lookup'], True);
			$found = false;
			foreach($rows as $k=>$v){
				foreach($v as $s){
					if(!isset($usTotal[date("U", strtotime("today", intval($s)))])){
						$usTotal[date("U", strtotime("today", intval($s)))] = 0;
					}
					if(!$found){
						$usTotal[date("U", strtotime("today", intval($s)))] = $usTotal[date("U", strtotime("today", intval($s)))]+1;
					$found = True;
					}
				}
			}
		}
		
		while($row = mysqli_fetch_assoc($nusResult)){
			$rows = json_decode($row['Lookup'], True);
			$found = false;
			foreach($rows as $k=>$v){
				foreach($v as $s){
					if(!isset($nusTotal[date("U", strtotime("today", intval($s)))])){
						$nusTotal[date("U", strtotime("today", intval($s)))] = 0;
					}
					if(!$found){
						$nusTotal[date("U", strtotime("today", intval($s)))] = $nusTotal[date("U", strtotime("today", intval($s)))]+1;
					$found = True;
					}
				}
			}
		}
		
		foreach($usTotal as $k => $v){
			if(!isset($nusTotal[date("U", strtotime("today", $k))])){
				$nusTotal[date("U", strtotime("today", $k))] = 0;
			}
		}
		foreach($nusTotal as $k => $v){
			if(!isset($usTotal[date("U", strtotime("today", $k))])){
				$usTotal[date("U", strtotime("today", $k))] = 0;
			}
		}
		
		ksort($usTotal);
		ksort($nusTotal);
		return [$usTotal, $nusTotal];
	}
	
	function dailyLookups($result){
		$lookups = array();
		
		while($row = mysqli_fetch_assoc($result)){
			$rows = json_decode($row['Lookup'], True);
			foreach($rows as $k=>$v){
				foreach($v as $s){
					if(isset($lookups[date("U", strtotime("today", intval($s)))])){
						$lookups[date("U", strtotime("today", intval($s)))] = $lookups[date("U", strtotime("today", intval($s)))]+1;
					}
					else {
						$lookups[date("U", strtotime("today", intval($s)))] = 1;
					}
				}
			}
		}
		
		ksort($lookups);
		return $lookups;
	}
?>

<html>
	<head>
		<title>Tide Aware Analytics</title>
		<script src="/bower_components/webcomponentsjs/webcomponents.min.js"></script>
		<link rel="import" href="/bower_components/core-icons/core-icons.html"></link>
		<link rel="import" href="/bower_components/core-icon/core-icon.html"></link>
		<link rel="stylesheet" href="bootstrap.min.css"></link>
		<link rel="shortcut icon" href="http://mikedombrowski.com/analytics/favicon.ico">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		<script src="/flot/jquery.bootstrap-autohidingnavbar.min.js"></script>
		<script src="/flot/jquery-ui.min.js"></script>
		<link rel="stylesheet" href="/flot/jquery-ui.min.css"></link>
		<style type="text/css">		
			body {padding-top: 50px;}
			.piechart {
				position: relative;
				width: 100%;
				height: 100%;
				max-height: 400px;
			}
			
			.zoom-plot {
				position: relative;
				width: 100%;
				height: 100%;
				max-height: 400px;
			}
			
			.flotTip {
				  padding: 3px 5px;
				  background-color: #000;
				  z-index: 100;
				  color: #fff;
				  box-shadow: 0 0 10px #555;
				  opacity: .7;
				  filter: alpha(opacity=70);
				  border: 2px solid #fff;
				  -webkit-border-radius: 4px;
				  -moz-border-radius: 4px;
				  border-radius: 4px;
			}
			
			.vdivide [class*='col-']:after {
			  background: #464545;
			  width: 1px;
			  content: "";
			  display:block;
			  position: absolute;
			  top:0;
			  bottom: 0;
			  right: 0;
			  min-height: 70px;
			}
			
			.ui-datepicker{
				z-index:1200 !important;
			}
			
			.big-icon {
				height: 1em;
				width: 1em;
				vertical-align: top;
			  }
		</style>
	</head>
	<body>
		<script>
			$(function() {
				$("#from").datepicker({
				  defaultDate: "-1w",
				  changeMonth: true,
				  numberOfMonths: 1,
				  onClose: function( selectedDate ) {
					$("#to").datepicker( "option", "minDate", selectedDate );
					document.location = "<?php echo "?start=";?>"+selectedDate<?php 
					if(isset($_GET['end'])){
						echo "+\"&end=".date("m/d/Y", $endDate)."\"";
					}?>;
				  }
				});
				$("#to").datepicker({
				  defaultDate: "today",
				  changeMonth: true,
				  numberOfMonths: 1,
				  onClose: function( selectedDate ) {
					$("#from").datepicker( "option", "maxDate", selectedDate );
					document.location = "<?php
					if(isset($_GET['start'])){
						echo "?start=".date("m/d/Y", $startDate)."&end=";
					}
					else {
						echo "?end=";
					}
					?>"+selectedDate;
				  }
				});
			});
		</script>
		<div class="navbar navbar-default navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<a href="/analytics/" class="navbar-brand">Tide Aware Analytics</a>
					<button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>
				<div class="navbar-collapse collapse" id="navbar-main">
					<ul class="nav navbar-nav navbar-right">
						<li>
							<a href="#versions">Versions</a>
						</li>
						<li>
							<a href="#uniques">Uniques</a>
						</li>
						<li>
							<a href="#new-returning">New vs. Returning</a>
						</li>
						<li>
							<a href="javascript:history.go(0)">
								<span class="glyphicon glyphicon-refresh" style="padding-top:3px;"></span>
							</a>
						</li>
						<li>
							<a href="#" data-toggle="dropdown" class="dropdown-toggle" role="button" aria-expanded="false" aria-haspopup="true">Time Frame <span class="caret"></span></a>
							<ul class="dropdown-menu" id="date">
								<li class="dropdown-header">Predefined Time Frame</li>
								<li><a id="past-month" href="?start=<?php echo date("m/d/Y", strtotime("-1 month", time()+$tz))."&end=".date("m/d/Y", time()+$tz);?>">Past Month</a></li>
								<li><a id="past-week" href="?start=<?php echo date("m/d/Y", strtotime("-1 week", time()+$tz))."&end=".date("m/d/Y", time()+$tz);?>">Past Week</a></li>
								<li><a id="past-day" href="?start=<?php echo date("m/d/Y", strtotime("-1 day", time()+$tz))."&end=".date("m/d/Y", time()+$tz);?>">Past Day</a></li>
								<li><a id="today-day" href="?start=<?php echo date("m/d/Y", time()+$tz)."&end=".date("m/d/Y", strtotime("+1 day", time()+$tz));?>">Today</a></li>
								<li class="divider" role="separator"></li>
								<li class="dropdown-header">Custom Time Frame</li>
								<li><a>
									<label for="from">From&nbsp;</label>
									<input type="text" id="from" name="from" placeholder="<?php echo date("m/d/Y", $startDate);?>"></a>
								</li>
								<li><a>
									<label for="to">To&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
									<input type="text" id="to" name="to" placeholder="<?php echo date("m/d/Y", $endDate);?>"></a>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			$(".navbar-fixed-top").autoHidingNavbar();
		</script>
		<div class="container-fluid">
			<div class="row vdivide">
				<div class="col-md-4">
					<script type="text/javascript">
						function since(){
							window.alert("Counting began <?php echo date("Y-m-d", 1439933414);?>");
						}
					</script>
					<script language="javascript" type="text/javascript" src="/flot/jquery.flot.min.js"></script>
					<script language="javascript" type="text/javascript" src="/flot/jquery.flot.pie.min.js"></script>
					<script language="javascript" type="text/javascript" src="/flot/jquery.flot.tooltip.min.js"></script>
					<script language="javascript" type="text/javascript" src="/flot/jquery.flot.time.min.js"></script>
					<script language="javascript" type="text/javascript" src="/flot/jquery.flot.selection.min.js"></script>
					<script language="javascript" type="text/javascript" src="/flot/jquery.flot.stack.min.js"></script>
					
					<h2><a href="#" onclick="since()">Total Number of Users Ever: <?php echo(intval(mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) from `Users`"))[0]));?></a></h2>
					<h3>Total US Users: <?php echo(intval(mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) from `Users` WHERE `US`='1'"))[0]));?></h3>
					<h3>Total Non-US Users: <?php echo(intval(mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) from `Users` WHERE `US`='0'"))[0]));?></h3>
					<hr>
					<h2>Total Number of Users: <?php 
						$currentResult = mysqli_query($link, "SELECT * from `Users` HAVING `Last Time` <= '".intval($endDate)."'"); 
						$oldResult = mysqli_query($link, "SELECT * from `Users` HAVING `Last Time`<= '".intval($endDate-($endDate-$startDate))."'");
						
						$allRes = compareUsers($currentResult, $oldResult, $startDate, $endDate);
						$current = intval($allRes[0]);
						$old = intval($allRes[1]);
						
						if($current>$old){
							echo $current."&nbsp;&nbsp;<core-icon icon='trending-up' class='big-icon' role='img' style='color: #4CAF50;'></core-icon>&nbsp;&nbsp;".intval($current-$old);
						}
						else if ($current == $old){
							echo $current."&nbsp;&nbsp;<core-icon icon='trending-neutral' class='big-icon' role='img' style='color: gray;'></core-icon>&nbsp;&nbsp;0";
						}
						else {
							echo $current."&nbsp;&nbsp;<core-icon icon='trending-down' class='big-icon' role='img' style='color: #E55943;'></core-icon>&nbsp;&nbsp;".intval($old-$current);
						}
					?></h2>
					<h3>US Users: <?php 
						$currentResult = mysqli_query($link, "SELECT * from `Users` WHERE `US` = '1' HAVING `Last Time`<= '".$endDate."'"); 
						$oldResult = mysqli_query($link, "SELECT * from `Users` WHERE `US` = '1' HAVING `Last Time`<= '".intval($endDate-($endDate-$startDate))."'");
						
						$res = compareUsers($currentResult, $oldResult, $startDate, $endDate);
						$current = intval($res[0]);
						$old = intval($res[1]);
						
						if($current>$old){
							echo $current."&nbsp;&nbsp;<core-icon icon='trending-up' class='big-icon' role='img' style='color: #4CAF50;'></core-icon>&nbsp;&nbsp;".intval($current-$old);
						}
						else if ($current == $old){
							echo $current."&nbsp;&nbsp;<core-icon icon='trending-neutral' class='big-icon' role='img' style='color: gray;'></core-icon>&nbsp;&nbsp;0";
						}
						else {
							echo $current."&nbsp;&nbsp;<core-icon icon='trending-down' class='big-icon' role='img' style='color: #E55943;'></core-icon>&nbsp;&nbsp;".intval($old-$current);
						}
					?></h3>
					<h3>Non-US Users: <?php 
						$currentResult = mysqli_query($link, "SELECT * from `Users` WHERE `US` = '0' HAVING `Last Time`<= '".$endDate."'"); 
						$oldResult = mysqli_query($link, "SELECT * from `Users` WHERE `US` = '0' HAVING `Last Time`<= '".intval($endDate-($endDate-$startDate))."'");
						
						$res = compareUsers($currentResult, $oldResult, $startDate, $endDate);
						$current = intval($res[0]);
						$old = intval($res[1]);
						
						if($current>$old){
							echo $current."&nbsp;&nbsp;<core-icon icon='trending-up' class='big-icon' role='img' style='color: #4CAF50;'></core-icon>&nbsp;&nbsp;".intval($current-$old);
						}
						else if ($current == $old){
							echo $current."&nbsp;&nbsp;<core-icon icon='trending-neutral' class='big-icon' role='img' style='color: gray;'></core-icon>&nbsp;&nbsp;0";
						}
						else {
							echo $current."&nbsp;&nbsp;<core-icon icon='trending-down' class='big-icon' role='img' style='color: #E55943;'></core-icon>&nbsp;&nbsp;".intval($old-$current);
						}
					?></h3>
					<hr>
					<h2>Total Lookups: <?php 
						$current = intval($allRes[2]);
						$old = intval($allRes[3]);
						
						if($current>$old){
							echo $current."&nbsp;&nbsp;<core-icon icon='trending-up' class='big-icon' role='img' style='color: #4CAF50;'></core-icon>&nbsp;&nbsp;".intval($current-$old);
						}
						else if ($current == $old){
							echo $current."&nbsp;&nbsp;<core-icon icon='trending-neutral' class='big-icon' role='img' style='color: gray;'></core-icon>&nbsp;&nbsp;0";
						}
						else {
							echo $current."&nbsp;&nbsp;<core-icon icon='trending-down' class='big-icon' role='img' style='color: #E55943;'></core-icon>&nbsp;&nbsp;".intval($old-$current);
						}
					?></h2>
					<hr>
					<h2>Average Lookups Per User: <?php 
						$result = mysqli_query($link, "SELECT `Lookup` from `Users`");
						$arr = array();
						while($row = mysqli_fetch_array($result)){
							$row = json_decode($row['Lookup'], True);
							foreach($row as $k=>$v){
								array_push($arr, count($v));
							}
						}
						echo substr(array_sum($arr) / count($arr), 0, 5);
					?></h2>
				</div>
				<div class="col-md-4">
					<?php
					$result = mysqli_query($link, "SELECT `Lookup` from `Users`; ");
					$arr = array();
					while($row = mysqli_fetch_array($result)){
						$row = json_decode($row['Lookup'], True);
						foreach($row as $k=>$v){
							if(isset($arr[$k])){
								$arr[$k] = $arr[$k]+count($v);
							}
							else {
								$arr[$k] = count($v);
							}
						}
					}
					arsort($arr);
					$alltime = array();
					foreach($arr as $k=>$v){
						array_push($alltime, $k);
						array_push($alltime, $v);
					}
					
					$result = mysqli_query($link, "SELECT `Lookup` from (SELECT `Lookup`, `Last Time` from (SELECT `Lookup`, `Last Time` from `Users` HAVING `Last Time` >= '".$startDate."') AS T HAVING `Last Time`< '".$endDate."') AS X");
					$arr = array();
					while($row = mysqli_fetch_array($result)){
						$row = json_decode($row['Lookup'], True);
						foreach($row as $k=>$v){
							foreach($v as $k2 => $v2){
								if($v2 >= $startDate && $v2 < $endDate){
									if(isset($arr[$k])){
										$arr[$k] = $arr[$k]+1;
									}
									else {
										$arr[$k] = 1;
									}
								}
							}
						}
					}
					arsort($arr);
					$day = array();
					foreach($arr as $k=>$v){
						array_push($day, $k);
						array_push($day, $v);
					}
					?>
					<h2>Top Locations of All Time:</h2> <div class="table-responsive"><table class="table table-striped"><thread><tr><th>Location</th><th>Number of Lookups</th></tr></thread>
					<tr><td><?php echo $alltime[0]?></td><td><?php echo $alltime[1]?></td></tr>
					<tr><td><?php echo $alltime[2]?></td><td><?php echo $alltime[3]?></td></tr>
					<tr><td><?php echo $alltime[4]?></td><td><?php echo $alltime[5]?></td></tr>
					</table></div>
					<hr>
					<h3>Top Locations:</h3><div class="table-responsive"><table class="table table-striped"><thread><tr><th>Location</th><th>Number of Lookups</th></tr></thread>
					<?php if(isset($day[0]) && isset($day[1])){echo "<tr><td>$day[0]</td><td>$day[1]</td></tr>";}?>
					<?php if(isset($day[2]) && isset($day[3])){echo "<tr><td>$day[2]</td><td>$day[3]</td></tr>";}?>
					<?php if(isset($day[4]) && isset($day[5])){echo "<tr><td>$day[4]</td><td>$day[5]</td></tr>";}?>
					</table></div>
				</div>
				<div class="col-md-4">
					<?php
					$result = mysqli_query($link, "SELECT `Lookup` from `Users`; ");
					$arr = array();
					while($row = mysqli_fetch_array($result)){
						$row = json_decode($row['Lookup'], True);
						foreach($row as $k=>$v){
							if(isset($arr[$k])){
								$arr[$k] = $arr[$k]+1;
							}
							else {
								$arr[$k] = 1;
							}
						}
					}
					arsort($arr);
					$alltime = array();
					foreach($arr as $k=>$v){
						array_push($alltime, $k);
						array_push($alltime, $v);
					}
										
					$result = mysqli_query($link, "SELECT `Lookup` from (SELECT `Lookup`, `Last Time` from (SELECT `Lookup`, `Last Time` from `Users` HAVING `Last Time` >= '".$startDate."') AS T HAVING `Last Time`< '".$endDate."') AS X");
					$arr = array();
					while($row = mysqli_fetch_array($result)){
						$row = json_decode($row['Lookup'], True);
						foreach($row as $k=>$v){
							if(isset($arr[$k])){
								$arr[$k] = $arr[$k]+1;
							}
							else {
								$arr[$k] = 1;
							}
						}
					}
					arsort($arr);
					$day = array();
					foreach($arr as $k=>$v){
						array_push($day, $k);
						array_push($day, $v);
					}
					?>
					<h2>Most Common Locations of All Time:</h2><div class="table-responsive"><table class="table table-striped"><thread><tr><th>Location</th><th>Number of Users</th></tr></thread>
					<tr><td><?php echo $alltime[0]?></td><td><?php echo $alltime[1]?></td></tr>
					<tr><td><?php echo $alltime[2]?></td><td><?php echo $alltime[3]?></td></tr>
					<tr><td><?php echo $alltime[4]?></td><td><?php echo $alltime[5]?></td></tr>
					</table></div>
					<hr>
					<h3>Most Common Locations:</h3><div class="table-responsive"><table class="table table-striped"><thread><tr><th>Location</th><th>Number of Users</th></tr></thread>
					<?php if(isset($day[0]) && isset($day[1])){echo "<tr><td>$day[0]</td><td>$day[1]</td></tr>";}?>
					<?php if(isset($day[2]) && isset($day[3])){echo "<tr><td>$day[2]</td><td>$day[3]</td></tr>";}?>
					<?php if(isset($day[4]) && isset($day[5])){echo "<tr><td>$day[4]</td><td>$day[5]</td></tr>";}?>
					</table></div>
				</div>
			</div>
			<hr width=100%>
			<div class="row vdivide" id="uniques">
				<div class="col-md-6">
					<h2>Daily Unique Users</h2>
					<script type="text/javascript">
						var timezone = new Date().getTimezoneOffset()*60*1000;
						document.cookie = "tz="+(timezone/1000);
						var origRange = {xaxis: {from: <?php echo date("U", strtotime("today", $startDate))*1000;?>+timezone, to: <?php echo date("U", strtotime("today", $endDate))*1000;?>+timezone}};
						
						function setTicks(plot, ranges){
							var data = plot.getData();
							var to = ranges['xaxis']['to'];
							var from = ranges['xaxis']['from'];
							if(ranges['xaxis']['to'] === null) {
								var max = 0;
								var min = Number.MAX_SAFE_INTEGER;
								for (var i=0; i<data.length; i++){
									var last = data[i].data[data[i].data.length-1][0];
									var first = data[i].data[0][0];
									if (last > max){
										max = last;
									}
									if (first < min){
										min = first;
									}
								}
								to = max;
								from = min;
							}
							
							difference = 4*((to - from)/360000); //difference in hours
							var ticks = [];
							if (difference/plot.width() < 10){
								ticks = ['1', 'day'];
							}
							else if (difference/plot.width() >= 10 && difference/plot.width() < 20){
								ticks = ['2', 'day'];
							}
							else if (difference/plot.width() >= 20 && difference/plot.width() < 30){
								ticks = ['3', 'day'];
							}
							else if (difference/plot.width() >= 30 && difference/plot.width() < 45){
								ticks = ['5', 'day'];
							}
							else if (difference/plot.width() >= 45 && difference/plot.width() < 60){
								ticks = ['7', 'day'];
							}
							else if (difference/plot.width() >= 60 && difference/plot.width() < 90){
								ticks = ['2', 'week'];
							}
							else if (difference/plot.width() >= 90 && difference/plot.width() < 120){
								ticks = ['1', 'month'];
							}
							else if (difference/plot.width() >= 120 && difference/plot.width() < 180){
								ticks = ['2', 'month'];
							}
							else if (difference/plot.width() >= 180 && difference/plot.width() < 240){
								ticks = ['3', 'month'];
							}
							else {
								ticks = ['6', 'month'];
							}
							
							plot.getOptions().xaxes[0].tickSize = ticks;
							plot.setupGrid();
							plot.draw();
						}
						
						$(function() {
							var d = <?php 
								$result = dailyUniques(mysqli_query($link, "SELECT * from `Users` WHERE `US` = '1'"), mysqli_query($link, "SELECT * from `Users` WHERE `US` = '0'"));
								$US = array();
								$nUS = array();
								foreach($result[0] as $s=>$v){
									array_push($US, array(intval($s), intval($v)));
								}
								foreach($result[1] as $s=>$v){
									array_push($nUS, array(intval($s), intval($v)));
								}
								echo "[{label: 'US Users', data: ".json_encode($US)."}, {label: 'Non-US Users', data: ".json_encode($nUS)."}];"
							?>							
							
							for (var i = 0; i < d[0]['data'].length; ++i) {
								d[0]['data'][i][0] *= 1000;
								d[0]['data'][i][0] += timezone;
							}
							for (var i = 0; i < d[1]['data'].length; ++i) {
								d[1]['data'][i][0] *= 1000;
								d[1]['data'][i][0] += timezone;
							}
							
							function weekendAreas(axes) {
								var markings = [],
									d = new Date(axes.xaxis.min);
								d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
								d.setUTCSeconds(0);
								d.setUTCMinutes(0);
								d.setUTCHours(0);
								var i = d.getTime();
								do {
									markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 }, color: "rgba(135, 190, 218, .9)" });
									i += 7 * 24 * 60 * 60 * 1000;
								} while (i < axes.xaxis.max);

								return markings;
							}
							
							var options = {
								xaxis: {
									mode: "time",
									tickSize: [2, "day"],
									tickLength: 5,
									min: (<?php echo date("U", strtotime("today", $startDate))*1000;?>+timezone)
								},
								yaxis: {
									tickDecimals: 0
								},
								selection: {
									mode: "x"
								},
								grid: {
									markings: weekendAreas,
									hoverable: true,
									clickable: true
								},
								series: {
									points: {
										show:true
									},
									lines: {
										show: true,
										lineWidth: 3,
										fill: true,
										fillColor: { colors:[{ opacity: .8 }, {opacity:1}] }
									},
									stack: true
								},
								tooltip: {
									show: true,
									content: "%x: %s = %y",
									shifts: {
									  x: 20,
									  y: 0
									},
									defaultTheme: false
								},
								colors: ["#375a7f", "#009871"]
							};
							
							
							var plot = $.plot("#visitors", d, options);
							var overview = $.plot("#overview", d, {
								series: {
									lines: {
										show: true,
										lineWidth: 4
									},
									shadowSize: 0
								},
								xaxis: {
									ticks: [],
									mode: "time"
								},
								yaxis: {
									ticks: [],
									min: 0,
									autoscaleMargin: 0.1
								},
								selection: {
									mode: "x"
								},
								grid: {
									markings: weekendAreas
								},
								legend: {
									show:false
								},
								colors: ["#375a7f", "#009871"]
							});
							
							setTicks(plot, origRange);
							overview.setSelection(origRange);
							
							$("#visitors").bind("plotselected", function (event, ranges) {
								$.each(plot.getXAxes(), function(_, axis) {
									var opts = axis.options;
									opts.min = ranges.xaxis.from;
									opts.max = ranges.xaxis.to;
								});
								plot.setupGrid();
								plot.draw();
								plot.clearSelection();
								overview.setSelection(ranges, true);
								
								setTicks(plot, ranges);
							});

							$("#overview").bind("plotselected", function (event, ranges) {
								plot.setSelection(ranges);
							});
							
							$("#overview").bind("plotunselected", function (event) {
								var axes = plot.getAxes(),
									xaxis = axes.xaxis.options,
									yaxis = axes.yaxis.options;
								xaxis.min = null;
								xaxis.max = null;
								yaxis.min = null;
								yaxis.max = null;
								plot.setupGrid();
								plot.draw();
								
								setTicks(plot, {xaxis: {from:plot.getOptions().xaxis.min , to:plot.getOptions().xaxes[0].max}});
							});
							
							$("#visitors").bind("plothover", function(event, pos, obj) {
								if (!obj) {
									return;
								}
								$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
							});

							$("#visitors").bind("plotclick", function (event, pos, item) {
								if (item) {
									$("#clickdata").text(" - click point " + item.dataIndex + " in " + item.series.label);
									plot.highlight(item.series, item.datapoint);
									if (!obj) {
										return;
									}
									$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
								}
							});
						});
					</script>
					<div class="zoom-plot">
						<div id="visitors" class="zoom-plot"></div>
					</div>
					<div class="zoom-plot" style="height:100px; width:98%; margin: 0 auto;">
						<div id="overview" class="zoom-plot"></div>
					</div>
				</div>
				<div class="col-md-6">
					<h2>Total Lookups in View: <span id="numLookup"></span></h2>
					<script type="text/javascript">
						$(function() {
							var d = <?php
								$result = dailyLookups(mysqli_query($link, "SELECT * from `Users`"));
								$arr = array();
								foreach($result as $s=>$v){
									array_push($arr, array(intval($s), intval($v)));
								}
								echo "[{label: 'Total Lookups', data: ".json_encode($arr)."}];"
							?>
							
							for (var i = 0; i < d[0]['data'].length; ++i) {
								d[0]['data'][i][0] *= 1000;
								d[0]['data'][i][0] += timezone;
							}

							function weekendAreas(axes) {
								var markings = [],
									d = new Date(axes.xaxis.min);
								d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
								d.setUTCSeconds(0);
								d.setUTCMinutes(0);
								d.setUTCHours(0);
								var i = d.getTime();
								do {
									markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 }, color: "rgba(135, 190, 218, .9)" });
									i += 7 * 24 * 60 * 60 * 1000;
								} while (i < axes.xaxis.max);

								return markings;
							}
							
							var options = {
								xaxis: {
									mode: "time",
									tickSize: [2, "day"],
									tickLength: 5,
									min: <?php echo date("U", strtotime("today", $startDate))*1000;?>+timezone
								},
								yaxis: {
									tickDecimals: 0
								},
								selection: {
									mode: "x"
								},
								grid: {
									markings: weekendAreas,
									hoverable: true,
									clickable: true
								},
								series: {
									points: {
										show:true
									},
									lines: {
										show: true,
										lineWidth: 3,
										fill: true,
										fillColor: { colors:[{ opacity: .8 }, {opacity:1}] }
									},
									stack: true
								},
								tooltip: {
									show: true,
									content: "%x: %s = %y",
									shifts: {
									  x: 20,
									  y: 0
									},
									defaultTheme: false
								},
								colors: ["#375a7f", "#009871"]
							};

							var plot2 = $.plot("#lookups", d, options);
							var overview2 = $.plot("#overview2", d, {
								series: {
									lines: {
										show: true,
										lineWidth: 4
									},
									shadowSize: 0
								},
								xaxis: {
									ticks: [],
									mode: "time"
								},
								yaxis: {
									ticks: [],
									min: 0,
									autoscaleMargin: 0.1
								},
								selection: {
									mode: "x"
								},
								grid: {
									markings: weekendAreas
								},
								legend: {
									show:false
								},
								colors: ["#375a7f", "#009871"]
							});
							
							overview2.setSelection(origRange);
							setTicks(plot2, origRange);
												
							function setSpan(ranges){
								var completeLookups = 0;
								for (i=0; i<d[0]['data'].length; i++){
									if(parseInt(d[0]['data'][i][0]) >= parseInt(ranges['xaxis']['from']) && parseInt(d[0]['data'][i][0]) <= parseInt(ranges['xaxis']['to'])){
										completeLookups = completeLookups + parseInt(d[0]['data'][i][1]);
									}
								}
								$("#numLookup").text(completeLookups);
							}
							
							setSpan(origRange);
							
							$("#lookups").bind("plotselected", function (event, ranges) {
								$.each(plot2.getXAxes(), function(_, axis) {
									var opts = axis.options;
									opts.min = ranges.xaxis.from;
									opts.max = ranges.xaxis.to;
								});
								plot2.setupGrid();
								plot2.draw();
								plot2.clearSelection();

								// don't fire event on the overview2 to prevent eternal loop
								setTicks(plot2, ranges);
								overview2.setSelection(ranges, true);
								setSpan(ranges);
							});

							$("#overview2").bind("plotselected", function (event, ranges) {
								
								plot2.setSelection(ranges);
								
								setSpan(ranges);
							});
							
							$("#overview2").bind("plotunselected", function (event) {
								var axes = plot2.getAxes(),
									xaxis = axes.xaxis.options,
									yaxis = axes.yaxis.options;
								xaxis.min = null;
								xaxis.max = null;
								yaxis.min = null;
								yaxis.max = null;
								plot2.setupGrid();
								plot2.draw();
								
								setSpan({xaxis: {from: plot2.getXAxes()[0]["min"], to: plot2.getXAxes()[0]["max"]}});
								setTicks(plot2, {xaxis: {from:plot2.getOptions().xaxes[0].min , to:plot2.getOptions().xaxes[0].max}});
							});
							
							$("#lookups").bind("plothover", function(event, pos, obj) {
								if (!obj) {
									return;
								}
								$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+")</span>");
							});

							$("#lookups").bind("plotclick", function (event, pos, item) {
								if (item) {
									$("#clickdata").text(" - click point " + item.dataIndex + " in " + item.series.label);
									plot.highlight(item.series, item.datapoint);
									if (!obj) {
										return;
									}
									$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
								}
							});
						});
					</script>
					<div class="zoom-plot">
						<div id="lookups" class="zoom-plot"></div>
					</div>
					<div class="zoom-plot" style="height:100px; width:98%; margin: 0 auto;">
						<div id="overview2" class="zoom-plot"></div>
					</div>
				</div>
			</div>
			<hr width=100%>
			<div class="row vdivide" id="versions">
				<div class="col-md-12">
					<h1>Version Analytics</h1>
					<hr>
					<div class="row">
						<div class="col-sm-3">
							<h2>All Versions</h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php echo json_encode(generateChart(mysqli_query($link, "SELECT `US Versions`, `Non US Versions`, `Date` FROM (SELECT `US Versions`, `Non US Versions`, `Date` FROM `Daily` HAVING `Date` >= '".$startDate."') AS X HAVING `Date` < '".$endDate."'")));
									?>;
									var placeholder = $("#placeholder");
									placeholder.unbind();
									$.plot(placeholder, data, {
										series: {
											pie: { 
												innerRadius: 0.65,
												show: true,
												stroke: {
													width: 0.1,
													color: '#222222'
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {
											show: true
										},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  },
										  colors: ["#375a7f", "#009871", "rgb(203,75,75)", "#D3C349", "#D39249","#7B68EE", "#FFF5EE",  "rgb(175,216,248)"]
									});
									placeholder.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});								
								});
								function labelFormatter(label, series) {
									return "<div style='font-size:8pt; text-align:center; padding:2px; color:white;'>" + label + "<br/>" + Math.round(series.percent) + "%</div>";
								}
							</script>
							<div id="placeholder" class="piechart"></div>
						</div>
						<div class="col-sm-3">
							<h2>US Versions</h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php echo json_encode(generateChart(mysqli_query($link, "SELECT `US Versions`, `Date` FROM (SELECT `US Versions`, `Date` FROM `Daily` HAVING `Date` >= '".$startDate."') AS X HAVING `Date` < '".$endDate."'")));?>;
									var placeholder2 = $("#placeholder2");
									placeholder2.unbind();
									$.plot(placeholder2, data, {
										series: {
											pie: { 
												innerRadius: 0.65,
												show: true,
												stroke: {
													width: 0.1,
													color: '#222222'
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: true},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  },
										colors: ["#375a7f", "#009871", "rgb(203,75,75)", "#D3C349", "#D39249","#7B68EE", "#FFF5EE",  "rgb(175,216,248)"]
									});
									placeholder2.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder2.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
							</script>
							<div id="placeholder2" class="piechart"></div>
						</div>
						<div class="col-sm-3">
							<h2>Non-US Versions</h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php echo json_encode(generateChart(mysqli_query($link, "SELECT `Non US Versions`, `Date` FROM (SELECT `Non US Versions`, `Date` FROM `Daily` HAVING `Date` >= '".$startDate."') AS X HAVING `Date` < '".$endDate."'")));?>;
									var placeholder3 = $("#placeholder3");
									placeholder3.unbind();
									$.plot(placeholder3, data, {
										series: {
											pie: { 
												innerRadius: 0.65,
												show: true,
												stroke: {
													width: 0.1,
													color: '#222222'
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: true},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  },
										colors: ["#375a7f", "#009871", "rgb(203,75,75)", "#D3C349", "#D39249","#7B68EE", "#FFF5EE",  "rgb(175,216,248)"]
									});
									placeholder3.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder3.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
							</script>
							<div id="placeholder3" class="piechart"></div>
						</div>
						<div class="col-sm-3">
							<h2>US vs. Non-US</h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php 
									echo json_encode(generateChart2(mysqli_query($link, "SELECT `US Versions`, `Non US Versions`, `Date` FROM (SELECT `US Versions`, `Non US Versions`, `Date` FROM `Daily` HAVING `Date` >= '".$startDate."') AS X HAVING `Date` < '".$endDate."'")));?>;
									var placeholder6 = $("#placeholder6");
									placeholder6.unbind();
									$.plot(placeholder6, data, {
										series: {
											pie: { 
												innerRadius: 0.65,
												show: true,
												stroke: {
													width: 0.1,
													color: '#222222'
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: true},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  },
										colors: ["#375a7f", "#009871", "rgb(203,75,75)", "#D3C349", "#D39249","#7B68EE", "#FFF5EE",  "rgb(175,216,248)"]
									});
									placeholder6.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder6.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
							</script>
							<div id="placeholder6" class="piechart"></div>
						</div>
					</div>
				</div>
			</div>
			<hr width="100%"></hr>
			
			<div class="row vdivide" id="new-returning">
				<div class="col-md-6">
					<h2>New vs. Returning</h2>
					<hr>
					<div class="row">
						<div class="col-sm-4">
							<h2>Past Month</h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php 
									$new = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".date("U", strtotime("-1 month", time()+$tz))."') AS X HAVING `First Time` >= '".date("U", strtotime("-1 month", time()+$tz))."') AS T"))[0];
									$returning = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".date("U", strtotime("-1 month", time()+$tz))."') AS X HAVING `First Time` < '".date("U", strtotime("-1 month", time()+$tz))."') AS T"))[0];
									$arr = [['label'=>"New Users", 'data'=>$new], ['label'=>"Returning Users", 'data'=>$returning]];
									echo json_encode($arr);
									?>;
									var placeholder4 = $("#placeholder4");
									placeholder4.unbind();
									$.plot(placeholder4, data, {
										series: {
											pie: { 
												innerRadius: 0.5,
												show: true,
												stroke: {
													width: 0.1,
													color: '#222222'
												},
												label: {
													show: true,
													radius: .65,
													background: {
														opacity: 0.7,
														color: '#FFF'
													}
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: false},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  },
										colors: ["#375a7f", "#009871", "rgb(203,75,75)", "#D3C349", "#D39249","#7B68EE", "#FFF5EE",  "rgb(175,216,248)"]
									});
									placeholder4.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder4.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
							</script>
							<div id="placeholder4" class="piechart"></div>
						</div>
						<div class="col-sm-4">
							<h2>Past Week</h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php 
									$new = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".date("U", strtotime("-1 week", time()+$tz))."') AS X HAVING `First Time` >= '".date("U", strtotime("-1 week", time()+$tz))."') AS T"))[0];
									$returning = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".date("U", strtotime("-1 week", time()+$tz))."') AS X HAVING `First Time` < '".date("U", strtotime("-1 week", time()+$tz))."') AS T"))[0];
									$arr = [['label'=>"New Users", 'data'=>$new], ['label'=>"Returning Users", 'data'=>$returning]];
									echo json_encode($arr);
									?>;
									var placeholder7 = $("#placeholder7");
									placeholder7.unbind();
									$.plot(placeholder7, data, {
										series: {
											pie: { 
												innerRadius: 0.5,
												show: true,
												stroke: {
													width: 0.1,
													color: '#222222'
												},
												label: {
													show: true,
													radius: .65,
													background: {
														opacity: 0.7,
														color: '#FFF'
													}
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: false},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  },
										colors: ["#375a7f", "#009871", "rgb(203,75,75)", "#D3C349", "#D39249","#7B68EE", "#FFF5EE",  "rgb(175,216,248)"]
									});
									placeholder7.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder7.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
							</script>
							<div id="placeholder7" class="piechart"></div>
						</div>
						<div class="col-sm-4">
							<h2><?php echo date("m/d/y", $startDate)." to ".date("m/d/y", $endDate);?></h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php 
									$new = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".$startDate."') AS X HAVING `First Time` >= '".$startDate."') AS T"))[0];
									$returning = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".$startDate."') AS X HAVING `First Time` < '".$startDate."') AS T"))[0];
									$arr = [['label'=>"New Users", 'data'=>$new], ['label'=>"Returning Users", 'data'=>$returning]];
									echo json_encode($arr);
									?>;
									var placeholder5 = $("#placeholder5");
									placeholder5.unbind();
									$.plot(placeholder5, data, {
										series: {
											pie: { 
												innerRadius: 0.5,
												show: true,
												stroke: {
													width: 0.1,
													color: '#222222'
												},
												label: {
													show: true,
													radius: .65,
													background: {
														opacity: 0.7,
														color: '#FFF'
													}
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: false},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  },
										colors: ["#375a7f", "#009871", "rgb(203,75,75)", "#D3C349", "#D39249","#7B68EE", "#FFF5EE",  "rgb(175,216,248)"]
									});
									placeholder5.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder5.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
							</script>
							<div id="placeholder5" class="piechart"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
