<?php
namespace SugarRestHarness;

require_once("lib/Randomizers/RandomizerAbstract.php");

class RandomizerCity extends RandomizerAbstract implements RandomizerInterface
{
    public $cities = array();
    
    /**
     * getRandomData()
     *
     * Returns a random city name.
     *
     * @param array $params - optional hash of parameters. Not used in this class.
     * @return string - a city name.
     */
    public function getRandomData($params)
    {
        $this->populate();
        return $this->cities[rand(0, (count($this->cities) - 1))];
    }
    
    
    /**
     * populate()
     *
     * Just creates a list of city names for random selection.
     */
    public function populate()
    {
        if (empty($this->cities)) {
            $this->cities = array(
                'New York',
                'Los Angeles',
                'Chicago',
                'Houston',
                'Philadelphia',
                'Phoenix',
                'San Antonio',
                'San Diego',
                'Dallas',
                'San Jose',
                'Austin',
                'Jacksonville',
                'San Francisco',
                'Indianapolis',
                'Columbus',
                'Fort Worth',
                'Charlotte',
                'Detroit',
                'El Paso',
                'Seattle',
                'Denver',
                'Washington',
                'Memphis',
                'Boston',
                'Nashville',
                'Baltimore',
                'Oklahoma City',
                'Portland',
                'Las Vegas',
                'Louisville',
                'Milwaukee',
                'Albuquerque',
                'Tucson',
                'Fresno',
                'Sacramento',
                'Long Beach',
                'Kansas City',
                'Mesa',
                'Atlanta',
                'Virginia Beach',
                'Omaha',
                'Colorado Springs',
                'Raleigh',
                'Miami',
                'Oakland',
                'Minneapolis',
                'Tulsa',
                'Cleveland',
                'Wichita',
                'New Orleans',
            );
        }
    }
}
