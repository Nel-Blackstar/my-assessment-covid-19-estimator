<?php
class Data
{
    public $region;
    public $periodType;
    public $timeToElapse;
    public $reportedCases;
    public $population;
    public $totalHospitalBeds;
}

class Region
{
    public $name;
    public $avgAge;
    public $avgDailyIncomeInUSD;
    public $avgDailyIncomePopulation;
}

class Impact
{
    public $currentlyInfected;
    public $infectionsByRequestedTime;
    public $severeCasesByRequestedTime;
    public $hospitalBedsByRequestedTime;
    public $casesForICUByRequestedTime;
    public $casesForVentilatorsByRequestedTime;
    public $dollarsInFlight;
}

class SevereImpact
{
    public $currentlyInfected;
    public $infectionsByRequestedTime;
    public $severeCasesByRequestedTime;
    public $hospitalBedsByRequestedTime;
    public $casesForICUByRequestedTime;
    public $casesForVentilatorsByRequestedTime;
    public $dollarsInFlight;
}



function covid19ImpactEstimator($data)
{
	$data= json_decode(json_encode((object) $data), FALSE);

	$impact=new Impact;
	
    $severeImpact=new SevereImpact;

    $days=0;
	
    $impact->currentlyInfected=$data->reportedCases*10;
    $severeImpact->currentlyInfected=$data->reportedCases*50;

    if($data->periodType=="days"){
		
        $factor=(int)($data->timeToElapse/3);
        $days=$data->timeToElapse;
		
        $impact->infectionsByRequestedTime=(int)number_format(($impact->currentlyInfected*pow(2, $factor)), 0, '.', '');
        $severeImpact->infectionsByRequestedTime=(int)number_format(($severeImpact->currentlyInfected*pow(2, $factor)), 0, '.', '');
    }
	

    if($data->periodType=="weeks"){
		

        $factor=(int)(($data->timeToElapse*7)/3);
        $days=$data->timeToElapse*7;

        $impact->infectionsByRequestedTime=(int)number_format(($impact->currentlyInfected*pow(2, $factor)), 0, '.', '');
        $severeImpact->infectionsByRequestedTime=(int)number_format(($severeImpact->currentlyInfected*pow(2, $factor)), 0, '.', '');
		
    }
	
    if($data->periodType=="months"){

        $factor=(int)(($data->timeToElapse*30)/3);
        $days=$data->timeToElapse*30;

        $impact->infectionsByRequestedTime=(int)number_format(($impact->currentlyInfected*pow(2, $factor)), 0, '.', '');
        $severeImpact->infectionsByRequestedTime=(int)number_format(($severeImpact->currentlyInfected*pow(2, $factor)), 0, '.', '');
		
    }

	$impact->severeCasesByRequestedTime=(int)number_format(($impact->infectionsByRequestedTime*0.15), 0, '.', '');
    $severeImpact->severeCasesByRequestedTime=(int)number_format(($severeImpact->infectionsByRequestedTime*0.15), 0, '.', '');

	$impact->hospitalBedsByRequestedTime=(int)number_format((ceil((float)($data->totalHospitalBeds*0.35))-$impact->severeCasesByRequestedTime), 0, '.', '');
    $severeImpact->hospitalBedsByRequestedTime=(int)number_format((ceil((float)($data->totalHospitalBeds*0.35))-$severeImpact->severeCasesByRequestedTime), 0, '.', '');
	if($impact->hospitalBedsByRequestedTime>0){
		$impact->hospitalBedsByRequestedTime-=1;
        $severeImpact->hospitalBedsByRequestedTime-=1;
	}

    $impact->casesForICUByRequestedTime=(int)number_format(($impact->infectionsByRequestedTime*0.05), 0, '.', '');
    $severeImpact->casesForICUByRequestedTime=(int)number_format(($severeImpact->infectionsByRequestedTime*0.05), 0, '.', '');

    $impact->casesForVentilatorsByRequestedTime=(int)number_format((int)($impact->infectionsByRequestedTime*0.02), 0, '.', '');
    $severeImpact->casesForVentilatorsByRequestedTime=(int)number_format((int)($severeImpact->infectionsByRequestedTime*0.02), 0, '.', '');

	$impact->dollarsInFlight=(int)number_format((float)(($impact->infectionsByRequestedTime*$data->region->avgDailyIncomePopulation)*$data->region->avgDailyIncomeInUSD/$days), 2, '.', '');
    $severeImpact->dollarsInFlight=(int)number_format((float)(($severeImpact->infectionsByRequestedTime*$data->region->avgDailyIncomePopulation)*$data->region->avgDailyIncomeInUSD/$days), 2, '.', '');

  return (['data'=>(array)$data,'impact'=>(array)$impact,'severeImpact'=>(array)$severeImpact]);
}