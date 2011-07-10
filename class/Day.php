<?php
/**
 * @author Andre Baumeier <hallo@andre-baumeier.de>
 * @link http://andre-baumeier.de
 * @copyright Copyright (c) 2011, Andre Baumeier
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */
class Day {
	private $_date;
	private $_weekday;
	
	private $_bericht=null;
	
	/**
	 * array(
	 * 'desc'=>$desc,'hours' =>$hours
	 * )
	 */
	private $_jobs=array();
	
	/**
	 * DateTime $date
	 */
	public function __construct(DateTime $date)
	{
		$this->_date=$date;
		$this->_weekday=$date->format('D');
	}
	
	public function addJob($description, $hours=null) {
		$this->_jobs[]=array(
			'description'=>$description,
			'hours'=>$hours
		);
	}
	
	public function getJobs()
	{
		return $this->_jobs;
	}
	
	public function setBericht(Bericht $bericht) {
		$this->_bericht=$bericht;
	}
	
	public function getBericht() {
		return $this->_bericht;
	}
	
	public function getDateTime()
	{
		return $this->_date;
	}
	
	public function getWeekday()
	{
		return $this->_weekday;
	}
}