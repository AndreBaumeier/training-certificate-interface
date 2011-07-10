<?php
/**
 * @author Andre Baumeier <hallo@andre-baumeier.de>
 * @link http://andre-baumeier.de
 * @copyright Copyright (c) 2011, Andre Baumeier
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */
class Calculator
{
	public static function searchDay(DateTime $needle,array $arrDays) {
		foreach ($arrDays as $day) {
			if($day->getDateTime()->format('d.m.Y')==$needle->format('d.m.Y'))
				return $day;
		}
		return null;
	}
	
	public static function fillAbentism($arrBerichte, $arrDays, $intUserId)
	{
		$sql='SELECT id, start, end, reason, hour FROM absenteeism WHERE (userId = '.$intUserId.' OR userId IS NULL)';
	    #debug($sql);
	    $erg=DB::query($sql);
	    $arr=DB::fetch_assoc($erg);
	    #debug($arr);
	    foreach ($arr as $absenteeism) {
			$date=new DateTime($absenteeism['start']);
			$end=new DateTime($absenteeism['end']);
			while($date<=$end) {
				$tmpDay=self::searchDay($date, $arrDays);
				$tmpDay->addJob($absenteeism['reason'], $absenteeism['hour']);
				date_add($date, date_interval_create_from_date_string('1 days'));
			}
		}
	}
	
	public static function fillReports($arrBerichte, $arrDays, $intUserId)
	{
		$sql='SELECT id, date, hour, description FROM reports WHERE (userId = '.$intUserId.' OR userId IS NULL)';
	    #debug($sql);
	    $erg=DB::query($sql);
	    $arr=DB::fetch_assoc($erg);
	    #debug($arr);
	    foreach ($arr as $absenteeism) {
			$date=new DateTime($absenteeism['date']);
			$end=new DateTime($absenteeism['date']);
			while($date<=$end) {
				$tmpDay=self::searchDay($date, $arrDays);
				$tmpDay->addJob($absenteeism['description'], $absenteeism['hour']);
				date_add($date, date_interval_create_from_date_string('1 days'));
			}
		}
	}
	
    public static function getDays($index=null)
    {
        $day=array(
            'MO'=>array(), 'DI'=>array(), 'MI'=>array(), 'DO'=>array(), 'FR'=>array(), 'SA'=>array(), 'SO'=>array()
        );
        if (is_null($index)) {
            return $day;
        } else {
            $numeric=array_keys($day);
            return $numeric[$index];
        }
    }
}