<?php
namespace SugarRestHarness\Randomizers;

class RandomizerStreetAddress extends RandomizerAbstract implements RandomizerInterface
{
    public $streets = array();
    public $streetTypes = array();
    
    
    /**
     * getRandomData()
     *
     * Returns a random street address.
     *
     * @param array $params - a hash of parameters. Not used in this class.
     * @return string - a random street address.
     */
    public function getRandomData($params)
    {
        if (empty($this->streets)) {
            $this->populate();
        }
        
        $streetNumber = rand(10, 10000);
        $streetName = $this->streets[rand(0, (count($this->streets) - 1))];
        $streetType = $this->streetTypes[rand(0, (count($this->streetTypes) - 1))];
        
        return "$streetNumber $streetName $streetType";
    }
    
    
    /**
     * populate()
     *
     * populates the lists of strings for random selection.
     */
    public function populate()
    {
        $this->streets = array(
            '1st',
            '2nd',
            '3rd',
            '4th',
            '5th',
            'West',
            'North',
            'East',
            'South',
            '2nd North',
            'Grove',
            'Hillcrest',
            'Laurel',
            'Valley',
            '8th West',
            'Woodland',
            'Wall',
            'Oxford',
            'Pheasant Run',
            'Heather',
            '3rd North',
            'Bay',
            'Jackson',
            'Linda',
            'Cedar',
            'Belmont',
            'Hawthorne',
            'Sycamore',
        );
        
        $this->streetTypes = array(
            'Drive',
            'Street',
            'Blvd',
            'Avenue',
            'Lane',
            'Court',
            'Run',
        );
    }
}
