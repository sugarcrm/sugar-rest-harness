<?php
namespace SugarRestHarness;

require_once("lib/Randomizers/RandomizerAbstract.php");

class RandomizerTitle extends RandomizerAbstract implements RandomizerInterface
{
    public $titles = array();
    
    /**
     * getRandomData()
     *
     * Returns a random job title.
     *
     * @param array $params - a hash of parameters. Not used in this class.
     * @return string - a job title.
     */
    public function getRandomData($params = array())
    {
        $this->populate();
        return $this->titles[rand(0, (count($this->titles) - 1))];
    }
    
    
    /**
     * populate()
     *
     * populates the list of job titles.
     */
    public function populate()
    {
        if (empty($this->titles)) {
            $this->titles = array(
                'Royal Umber',
                'Ninja Master',
                'Dark Overload',
                'Chamberlain of Doom',
                'Pirate Lord',
                'Grand Thief',
                'Starbuckaneer',
                'Sorcerer on High',
                'Kraken Keeper',
                'Watcher of Things',
                'Captain',
                'First Mate',
                'General Mayhem',
                'Chaosian',
                'CEO of Confusion',
                'Sith Apprentice',
                'Financial Artisan',
                'Geek du Jour',
                'Cloud Entity',
                'Cave Dweller',
                'Romulan Spy',
                'Gladiator',
                'Worker Bee',
                'Cog in the Machine',
                'Dragon Rider',
                'Lovely Deceiver',
                'Dark Machinist',
                'Master Chef',
                'Big Bad Baker',
                'Hamburgerologist',
                'Cheese Snarfer',
                'Cake Eater',
                'Cookie Monster',
                'Whiskey Brigand',
                'Iron Conductor',
                'Fleeting Memory',
            );
        }
    }
}
