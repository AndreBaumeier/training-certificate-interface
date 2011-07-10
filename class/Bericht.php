<?php
/**
 * 
 * class for storing weekly reports
 * @author Andre Baumeier
 *
 */
class Report {
	private $_startDate;
	private $_endDate;
	private $_days=array();
	
	public function __construct($startDate, $endDate) {
		$this->_startDate=$startDate;
		$this->_endDate=$endDate;
	}
	
	public function addDay(Day $day) {
		$this->_days[]=$day;
		return $this;
	}
	
	public function getDays() {
		return $this->_days;
	}
	
	public function getDay($weekday) {
		foreach ($this->_days as $day) {
			if ($day->getWeekday()==$weekday) {
				return $day;
			}
		}
		return null;
	}
	
	public function getStart() {
		return $this->_startDate;
	}
	
	public function getEnd() {
		return $this->_endDate;
	}
	
	public function hasJobs() {
		foreach ($this->getDays() as $day) {
			$jobs=$day->getJobs();
			if (!empty($jobs)){
				return true;
			}
		}
		return false;
	}
	
	public function countWeekHour() {
		$hour=0;
		foreach ($this->getDays() as $day) {
			$jobs=$day->getJobs();
			if (!empty($jobs)){
				foreach ($jobs as $job) {
					if ($day->getWeekday()!="Sat"&&$day->getWeekday()!="Sun") {
					$hour+=$job['hours'];
					}
				}
			}
		}
		return $hour;
	}
}