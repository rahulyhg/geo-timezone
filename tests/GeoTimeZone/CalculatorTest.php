<?php

namespace Tests\GeoTimeZone;

use GeoTimeZone\Calculator;
use PHPUnit\Runner\Exception;
use Tests\AbstractUnitTestCase;

class CalculatorTest extends AbstractUnitTestCase
{
    const DATA_DIRECTORY = "/../../data/";
    
    protected $calculator;
    
    protected function setUp()
    {
        $this->calculator = new Calculator(__DIR__ . self::DATA_DIRECTORY);
    }
    
    public function getDataLocalDate()
    {
        return array(
            'Testing Lisbon' => array(
                'latitude' => 41.142700,
                'longitude' => -8.612150,
                'timestamp' => 1458844434,
                'expectedTimeZone' => 'Europe/Lisbon',
                '$expectedOffset' => 0
            ),
            'Testing Madrid' => array(
                'latitude' => 39.452800,
                'longitude' => -0.347038,
                'timestamp' => 1469387760,
                'expectedTimeZone' => 'Europe/Madrid',
                '$expectedOffset' => 7200
            )
        );
    }
    
    public function getDataCorrectTimestamp()
    {
        return array(
            'Testing Lisbon' => array(
                'latitude' => 41.142700,
                'longitude' => -8.612150,
                'timestamp' => 1458844434,
                'expectedTimestamp' => 1458844434,
            ),
            'Testing Madrid' => array(
                'latitude' => 39.452800,
                'longitude' => -0.347038,
                'timestamp' => 1469387760,
                'expectedTimestamp' => 1469380560,
            )
        );
    }
    
    public function getDataTimeZoneName()
    {
        return array(
            'Testing Lisbon' => array(
                'latitude' => 41.142700,
                'longitude' => -8.612150,
                'expectedTimeZone' => 'Europe/Lisbon',
            ),
            'Testing Madrid' => array(
                'latitude' => 39.452800,
                'longitude' => -0.347038,
                'expectedTimeZone' => 'Europe/Madrid',
            )
        );
    }
    
    public function getDataWrongLatitude()
    {
        return array(
            'Testing Wrong Latitude' => array(
                'latitude' => 10000000,
                'longitude' => -8.612150
            ),
            'Testing Null Latitude' => array(
                'latitude' => null,
                'longitude' => -8.612150
            )
        );
    }
    
    public function getDataWrongLongitude()
    {
        return array(
            'Testing Wrong Longitude' => array(
                'latitude' => 41.142700,
                'longitude' => 10000000,
            
            ),
            'Testing Null Longitude' => array(
                'latitude' => 41.142700,
                'longitude' => null,
            )
        );
    }
    
    public function getDataMaxLatitude()
    {
        return array(
            'Testing Positive Max Latitude' => array(
                'latitude' => 90.0,
                'longitude' => -8.612150,
                'adjustedLatitude' => 89.9999
            ),
            'Testing Negative Max Latitude' => array(
                'latitude' => -90.0,
                'longitude' => -8.612150,
                'adjustedLatitude' => -89.9999
            )
        );
    }
    
    public function getDataMaxLongitude()
    {
        return array(
            'Testing Positive Max Longitude' => array(
                'latitude' => -8.612150,
                'longitude' => 180.0,
                'adjustedLongitude' => 179.9999
            ),
            'Testing Negative Max Longitude' => array(
                'latitude' => -8.612150,
                'longitude' => -180.0,
                'adjustedLongitude' => -179.9999
            )
        );
    }
    
    /**
     * @dataProvider getDataWrongLatitude
     * @param $latitude
     * @param $longitude
     */
    public function testGetTimeZoneNameWithWrongLatitude($latitude, $longitude)
    {
        try {
            $timeZone = $this->calculator->getTimeZoneName($latitude, $longitude);
            $this->assertEquals($timeZone, "none");
        } catch (Exception $error) {
            $this->expectException(\ErrorException::class);
            $this->expectExceptionMessage("Invalid latitude: {$latitude}");
        }
    }
    
    /**
     * @dataProvider getDataWrongLongitude
     * @param $latitude
     * @param $longitude
     */
    public function testGetTimeZoneNameWithWrongLongitude($latitude, $longitude)
    {
        try {
            $timeZone = $this->calculator->getTimeZoneName($latitude, $longitude);
            $this->assertEquals($timeZone, "none");
        } catch (Exception $error) {
            $this->expectException(\ErrorException::class);
            $this->expectExceptionMessage("Invalid longitude: {$longitude}");
        }
    }
    
    /**
     * @dataProvider getDataMaxLatitude
     * @param $latitude
     * @param $longitude
     */
    public function testGetTimeZoneNameWithMaxLatitude($latitude, $longitude)
    {
        $timeZone = $this->calculator->getTimeZoneName($latitude, $longitude);
        $this->assertTrue(is_string($timeZone));
    }
    
    /**
     * @dataProvider getDataMaxLongitude
     * @param $latitude
     * @param $longitude
     */
    public function testGetTimeZoneNameWithMaxLongitude($latitude, $longitude)
    {
        $timeZone = $this->calculator->getTimeZoneName($latitude, $longitude);
        $this->assertTrue(is_string($timeZone));
    }
    
    /**
     * @dataProvider getDataMaxLatitude
     * @param $latitude
     * @param $longitude
     * @param $adjustedLatitude
     */
    public function testAdjustLatitudeWithMaxLatitude($latitude, $longitude, $adjustedLatitude)
    {
        $method = $this->getPrivateMethod(get_class($this->calculator), 'adjustLatitude');
        $latitudeToTest = $method->invokeArgs($this->calculator, array($latitude));
        $this->assertEquals($adjustedLatitude, $latitudeToTest);
    }
    
    /**
     * @dataProvider getDataMaxLongitude
     * @param $latitude
     * @param $longitude
     * @param $adjustedLongitude
     */
    public function testAdjustMaxLongitudeWithMaxLongitude($latitude, $longitude, $adjustedLongitude)
    {
        $method = $this->getPrivateMethod(get_class($this->calculator), 'adjustLongitude');
        $longitudeToTest = $method->invokeArgs($this->calculator, array($longitude));
        $this->assertEquals($adjustedLongitude, $longitudeToTest);
    }
    
    /**
     * @dataProvider getDataLocalDate
     * @param $latitude
     * @param $longitude
     * @param $timestamp
     * @param $expectedTimeZone
     * @param $expectedOffset
     */
    public function testGetLocalDate($latitude, $longitude, $timestamp, $expectedTimeZone, $expectedOffset)
    {
        $localDate = $this->calculator->getLocalDate($latitude, $longitude, $timestamp);
        
        $this->assertInstanceOf('DateTime', $localDate);
        $this->assertEquals(
            $localDate->getTimezone()->getName(),
            $expectedTimeZone
        );
        $this->assertEquals(
            $localDate->getOffset(),
            $expectedOffset
        );
    }
    
    /**
     * @dataProvider getDataCorrectTimestamp
     * @param $latitude
     * @param $longitude
     * @param $timestamp
     * @param $expectedTimestamp
     */
    public function testGetCorrectTimestamp($latitude, $longitude, $timestamp, $expectedTimestamp)
    {
        $correctTimestamp = $this->calculator->getCorrectTimestamp($latitude, $longitude, $timestamp);
        $this->assertEquals($correctTimestamp, $expectedTimestamp);
    }
    
    /**
     * @dataProvider getDataTimeZoneName
     * @param $latitude
     * @param $longitude
     * @param $expectedTimeZone
     */
    public function testGetTimeZoneName($latitude, $longitude, $expectedTimeZone)
    {
        $timeZoneName = $this->calculator->getTimeZoneName($latitude, $longitude);
        $this->assertEquals($timeZoneName, $expectedTimeZone);
    }
}
