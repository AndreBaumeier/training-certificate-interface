<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: text/html; charset=utf-8");
require_once('class/DB.php');
require_once('class/Bericht.php');
require_once('class/Day.php');
require_once('class/Calculator.php');
require_once('class/Template.php');

function debug($output) {
	if (!is_string($output)) {
		echo '<div style="border:2px solid yellow; width:400px;background-color:grey;margin:5px;"><pre>';
		var_dump($output);
		echo '</pre></div>';
	} else {
		echo '<div style="border:2px solid yellow; width:400px;background-color:grey;margin:5px;">'.$output.'</div>';
	}
}
echo Template::header();


$sql='SELECT id, forename, surname, dateofbirth, birthplace, address, job, company, begin, end FROM user WHERE id = 1';
$erg=DB::query($sql);
$info=DB::fetch_assoc($erg);
$userinfo=$info[0];
$userinfo['begin'] = new DateTime($userinfo['begin']);
$userinfo['end'] = new DateTime($userinfo['end']);

#var_dump($userinfo['begin']<$userinfo['end']);

$date=$userinfo['begin'];
$arrAllData=array();
$arrDays=array();
$i=0;
while ($date<=$userinfo['end']) {
	#echo $date->format('d.m.Y');
	#echo '<hr />';
	$weekEnd=clone($date);
	date_add($weekEnd, date_interval_create_from_date_string('6 days'));
	$report=new Report(clone($date), clone($weekEnd));
	
	#$arrDays[]=clone()
	$mo=clone($date);
	$tu=clone($mo);
	date_add($tu, date_interval_create_from_date_string('1 days'));
	$we=clone($tu);
	date_add($we, date_interval_create_from_date_string('1 days'));
	$th=clone($we);
	date_add($th, date_interval_create_from_date_string('1 days'));
	$fr=clone($th);
	date_add($fr, date_interval_create_from_date_string('1 days'));
	$sa=clone($fr);
	date_add($sa, date_interval_create_from_date_string('1 days'));
	$su=clone($sa);
	date_add($su, date_interval_create_from_date_string('1 days'));
	
	$objMo=new Day($mo);
	$arrDays[]=$objMo;
	
	$objTu=new Day($tu);
	$arrDays[]=$objTu;
	
	$objWe=new Day($we);
	$arrDays[]=$objWe;
	
	$objTh=new Day($th);
	$arrDays[]=$objTh;
	
	$objFr=new Day($fr);
	$arrDays[]=$objFr;
	
	$objSa=new Day($sa);
	$arrDays[]=$objSa;
	
	$objSu=new Day($su);
	$arrDays[]=$objSu;
	
	$report->addDay($objMo)
			->addDay($objTu)
			->addDay($objWe)
			->addDay($objTh)
			->addDay($objFr)
			->addDay($objSa)
			->addDay($objSu);
	
	date_add($date, date_interval_create_from_date_string('7 days'));
	$arrAllData[]=$report;
}
#var_dump($arrAllData);
#echo '<hr />';
#echo '<hr />';
#var_dump($arrDays);
Calculator::fillAbentism($arrAllData, $arrDays, 1);
Calculator::fillReports($arrAllData, $arrDays, 1);

$withJobs=0;
$withoutJobs=0;
$complete=0;

$todo=array();
$number=1;
foreach ($arrAllData as $bericht) {
	$weekHours=$bericht->countWeekHour();
	if ($bericht->hasJobs()) {
		
		$backgroundColor='orange';
		if ($weekHours>=38) {
			$complete++;
			$backgroundColor='green';
			if ($weekHours>40) {
				$backgroundColor='yellow';
			}
		} else {
			$withJobs++;
		}
	} else {
		$withoutJobs++;
		$backgroundColor='red';
	}
	
	echo '<div style="width:320px;float:left;height:400px;overflow:scroll;background-color:'.$backgroundColor.';">';
	echo $weekHours.' Wochenstunden';
	echo '<br />';
	echo '<hr />';
	echo 'Start: '.$bericht->getStart()->format('Y-m-d');
	echo '<br />';
	echo 'Ende: '.$bericht->getEnd()->format('Y-m-d');
	echo '<br />Wochentage:<br />';
	foreach ($bericht->getDays() as $day) {
		#echo $day->getWeekday().' (<input type="text" value="'.$day->getDateTime()->format('Y-m-d').'" onClick="this.focus();this.select();" />):<br />';
		echo $day->getWeekday().' ('.$day->getDateTime()->format('Y-m-d').'):<br />';
		
		$jobs=$day->getJobs();
		foreach ($jobs as $job) {
			echo $job['description'];
			echo ' ('.$job['hours'].')';
			echo '<br />';
		}
		
		if (empty($jobs)&&$day->getWeekday()!="Sat"&&$day->getWeekday()!="Sun") {
			$todo[]=$day->getDateTime()->format('Y-m-d');
		}
		
		echo '<br />';
	}
	echo '</div>';
	
	Template::createPDF($bericht, $number);
	$number++;
	#exit();
}

echo 'Komplett: '.$complete;
echo '<br />';
echo 'Teilweise: '.$withJobs;
echo '<br />';
echo 'ohne: '.$withoutJobs;

echo '<br />Todo: '.count($todo).'<br />';
echo implode('<br />', $todo);

echo Template::footer();

/*
 * This was for inserting school days, could also be used for every other day series.
 * 
$bdays="2010-05-03
2010-05-10
2010-05-17
2010-05-31
2010-06-07
2010-06-14
2010-06-21
2010-06-28
2010-07-05
2010-07-12
2010-08-30
2010-09-06
2010-09-13
2010-09-20
2010-09-27
2010-10-04
2010-10-25
2010-11-08
2010-11-15
2010-11-22
2010-11-29
2010-12-06
2010-12-13
2010-12-20
2011-01-10
2011-01-17
2011-01-24
2011-01-31
2011-02-07
2011-02-14
2011-02-21
2011-02-28
2011-03-07
2011-03-14
2011-03-21
2011-03-28
2011-04-04
2011-04-11
2011-05-02
2011-05-09
2011-05-16
2011-05-23
2011-05-30
2011-06-06
2011-06-20";
$d=explode("\n", $bdays);
echo '<div style="clear:both;"></div>';
foreach ($d as $ds) {
	$sql="INSERT INTO reports SET userId = 1, date = '$ds', `hour` = 8, description = 'Berufsschule'";
	echo $sql.'<br />';
	#DB::query($sql);
} */