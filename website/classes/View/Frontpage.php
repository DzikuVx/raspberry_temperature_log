<?php

namespace View;

use General\Formater;

use General\Templater;

use Database\Factory;

abstract class Frontpage extends Base {

	protected $model = null;
	
	protected $modelName = '';
	
	public function __construct(array $aParams) {
		parent::__construct($aParams);
		
		$this->model = new $this->modelName();
		
	}
	
	public function mainpage()
	{
		$oTemplate = new Templater('mainpage.html');

		$oCurrent = $this->model->getCurrent();
		$oTemplate->add($oCurrent);

		$oData = $this->model->getAverage(1);
		$oTemplate->add('1dTempAvg', Formater::formatFloat($oData->Temperature, 2));
		$oTemplate->add('1dHumidityAvg', Formater::formatFloat($oData->Humidity, 2));
		
		$oData = $this->model->getMin(1);
		$oTemplate->add('1dTempMin', Formater::formatFloat($oData->Temperature, 2));
		$oTemplate->add('1dHumidityMin', Formater::formatFloat($oData->Humidity, 2));
		
		$oData = $this->model->getMax(1);
		$oTemplate->add('1dTempMax', Formater::formatFloat($oData->Temperature, 2));
		$oTemplate->add('1dHumidityMax', Formater::formatFloat($oData->Humidity, 2));
		
		$oData = $this->model->getAverage(7);
		$oTemplate->add('7dTempAvg', Formater::formatFloat($oData->Temperature, 2));
		$oTemplate->add('7dHumidityAvg', Formater::formatFloat($oData->Humidity, 2));
		
		$oData = $this->model->getMin(7);
		$oTemplate->add('7dTempMin', Formater::formatFloat($oData->Temperature, 2));
		$oTemplate->add('7dHumidityMin', Formater::formatFloat($oData->Humidity, 2));
		
		$oData = $this->model->getMax(7);
		$oTemplate->add('7dTempMax', Formater::formatFloat($oData->Temperature, 2));
		$oTemplate->add('7dHumidityMax', Formater::formatFloat($oData->Humidity, 2));

		return (string) $oTemplate;
		
	}
	
	public function tables()
	{
		$oTemplate = new Templater('tables.html');

		/*
		 * Get readout history
		 */
		$aHistory = $this->model->getHistory();

		$sTable = '';
		foreach ($aHistory as $iIndex => $oReadout) {
			
			$sTable .= '<tr>';
			$sTable .= '<td>'.($iIndex+1).'</td>';
			$sTable .= '<td>'.Formater::formatDateTime($oReadout['Date']).'</td>';
			$sTable .= '<td>'.$oReadout['Temperature'].'&deg;C</td>';
			$sTable .= '<td>'.$oReadout['Humidity'].'%</td>';
			$sTable .= '</tr>';
			
		}
		$oTemplate->add('Table', $sTable);
		
		/*
		 * Get daily aggregate
		 */
		$aHistory = $this->model->getDayAggregate(14);
		$sTable = '';
		foreach ($aHistory as $iIndex => $oReadout) {
			
			$sTable .= '<tr>';
			$sTable .= '<td>'.Formater::formatDate($oReadout['Date']).'</td>';
			$sTable .= '<td>'.Formater::formatFloat($oReadout['MinTemperature'],2).'&deg;C</td>';
			$sTable .= '<td>'.Formater::formatFloat($oReadout['Temperature'],2).'&deg;C</td>';
			$sTable .= '<td>'.Formater::formatFloat($oReadout['MaxTemperature'],2).'&deg;C</td>';
			$sTable .= '<td>'.Formater::formatFloat($oReadout['MinHumidity'],2).'%</td>';
			$sTable .= '<td>'.Formater::formatFloat($oReadout['Humidity'],2).'%</td>';
			$sTable .= '<td>'.Formater::formatFloat($oReadout['MaxHumidity'],2).'%</td>';
			$sTable .= '</tr>';
			
		}
		$oTemplate->add('DailyTable', $sTable);
		
		return (string) $oTemplate;
	}
	
	public function charts()
	{
		$oTemplate = new Templater('charts.html');
	
		return (string) $oTemplate;
	}

	/**
	 * render average temperature chart head for google charts
	 * @return string
	 */
	public function chartHead() {
		
		$oTemplate = new Templater('chartHead.html');
		
		/*
		 * Hour Aggregate charts
		 */
		$aHistory = $this->model->getHourAggregate(72,"ASC");
		
		$oChartHourTemperature = new \General\GoogleChart();
		$oChartHourTemperature->setTitle('Temperature');
		$oChartHourTemperature->setDomID('chartHourTemperature');
		$oChartHourTemperature->add('Hour', array());
		$oChartHourTemperature->add('Avg', array());
		$oChartHourTemperature->add('Max', array());
		$oChartHourTemperature->add('Min', array());
		
		$oChartHourHumidity = new \General\GoogleChart();
		$oChartHourHumidity->setTitle('Humidity');
		$oChartHourHumidity->setDomID('chartHourHumidity');
		$oChartHourHumidity->add('Hour', array());
		$oChartHourHumidity->add('Avg', array());
		$oChartHourHumidity->add('Max', array());
		$oChartHourHumidity->add('Min', array());

		foreach ($aHistory as $iIndex => $oReadout) {
			
			$oChartHourTemperature->push('Hour', Formater::formatTime($oReadout['Date']));
			$oChartHourTemperature->push('Avg', number_format($oReadout['Temperature'],2));
			$oChartHourTemperature->push('Max', number_format($oReadout['MaxTemperature'],2));
			$oChartHourTemperature->push('Min', number_format($oReadout['MinTemperature'],2));
			
			$oChartHourHumidity->push('Hour', Formater::formatTime($oReadout['Date']));
			$oChartHourHumidity->push('Avg', number_format($oReadout['Humidity'],2));
			$oChartHourHumidity->push('Max', number_format($oReadout['MaxHumidity'],2));
			$oChartHourHumidity->push('Min', number_format($oReadout['MinHumidity'],2));
			
		}
		$oTemplate->add('chartHourTemperature',$oChartHourTemperature->getHead());
		$oTemplate->add('chartHourHumidity',$oChartHourHumidity->getHead());

		/*
		 * Day aggregate charts
		 */
		
		$aHistory = $this->model->getDayAggregate(14,"ASC");
		
		$oChartDailyTemperature = new \General\GoogleChart();
		$oChartDailyTemperature->setTitle('Temperature');
		$oChartDailyTemperature->setDomID('chartDailyTemperature');
		$oChartDailyTemperature->add('Day', array());
		$oChartDailyTemperature->add('Avg', array());
		$oChartDailyTemperature->add('Max', array());
		$oChartDailyTemperature->add('Min', array());
		
		$oChartDailyHumidity = new \General\GoogleChart();
		$oChartDailyHumidity->setTitle('Humidity');
		$oChartDailyHumidity->setDomID('chartDailyHumidity');
		$oChartDailyHumidity->add('Day', array());
		$oChartDailyHumidity->add('Avg', array());
		$oChartDailyHumidity->add('Max', array());
		$oChartDailyHumidity->add('Min', array());
		
		foreach ($aHistory as $iIndex => $oReadout) {
				
			$oChartDailyTemperature->push('Day', Formater::formatDate($oReadout['Date']));
			$oChartDailyTemperature->push('Avg', number_format($oReadout['Temperature'],2));
			$oChartDailyTemperature->push('Max', number_format($oReadout['MaxTemperature'],2));
			$oChartDailyTemperature->push('Min', number_format($oReadout['MinTemperature'],2));
				
			$oChartDailyHumidity->push('Day', Formater::formatDate($oReadout['Date']));
			$oChartDailyHumidity->push('Avg', number_format($oReadout['Humidity'],2));
			$oChartDailyHumidity->push('Max', number_format($oReadout['MaxHumidity'],2));
			$oChartDailyHumidity->push('Min', number_format($oReadout['MinHumidity'],2));
				
		}
		$oTemplate->add('chartDailyTemperature',$oChartDailyTemperature->getHead());
		$oTemplate->add('chartDailyHumidity',$oChartDailyHumidity->getHead());
		
		return (string) $oTemplate;
		
	}
	
}