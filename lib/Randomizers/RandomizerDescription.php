<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Randomizers;

class RandomizerDescription extends RandomizerAbstract implements RandomizerInterface
{
    public $adj = array();
    public $nouns = array();
    public $events = array();
    public $colors = array();
    
    /**
     * getRandomData()
     *
     * Returns a string of random words. You can specify the order and type of
     * random words with $params['pattern'], e.g. $params['pattern'] = 'color noun';
     *
     * @param array $params - a hash of optional params. May include 'pattern',
     *  where the pattern is words that correspond to properties of this class that
     *  are lists of words which may be selected from randomly.
     * @return string - a string of random words.
     */
    public function getRandomData($params = array())
    {
        $this->populate();
        $description = array();
        
        if (isset($params['pattern'])) {
            $pattern = $params['pattern'];
        } else {
            $pattern = "adj noun";
        }
        
        $elements = explode(' ', $pattern);
        
        foreach ($elements as $el) {
            if (property_exists($this, $el)) {
                if (is_array($this->$el)) {
                    $data = $this->$el;
                    $description[] = $data[rand(0, (count($this->$el) - 1))];
                }
            } else {
                $description[] = $el;
            }
        }
        $string = implode(' ', $description);
        
        if (!empty($params['maxLength'])) {
            $string = substr($string, 0, $params['maxLength']);
        }
        
        return $string;
    }
    
    
    /**
     * populate()
     *
     * populates the arrays of words for random selection.
     *
     * @return void.
     */
    public function populate()
    {
        if (empty($this->color)) {
            $this->color = array(
                'Ivory',
                'Beige',
                'Wheat',
                'Tan',
                'Khaki',
                'Silver',
                'Gray',
                'Charcoal',
                'Navy Blue',
                'Royal Blue',
                'Blue',
                'Azure',
                'Cyan',
                'Aquamarine',
                'Teal',
                'Forest Green',
                'Olive',
                'Lime',
                'Golden',
                'Coral',
                'Pink',
                'Lavender',
                'Plum',
                'Indigo',
                'Maroon',
                'Crimson',
                'Green',
                'Yellow',
                'Red',
                'Orange',
                'Violet',
                'White',
                'Black',
                'Gray',
                );
                
            $this->adj = array(
                'Mega',
                'Uber',
                'Dandy',
                'Smiley',
                'Happy',
                'Crazy',
                'Smart',
                'Savory',
                'Corrugated',
                'Prickly',
                'Spiffy',
                'Sparkling',
                'Outstanding',
                'Smashing',
                'Shaken',
                'Shiny',
                'Somber',
                'Cloudy',
                'Flat',
                'Pristine',
                'Majestic',
                'Alpine',
                'Practical',
                'Itsy Bitsy',
                'Zany',
                'Rosy',
                'Cracking',
                'Wicked',
                'Demented',
                'Shocking',
                'Horrible',
                'Secret',
                'Iron',
                'Rusty',
                'Steam Powered',
                );
                
            $this->noun = array(
                'Chowder',
                'Crab',
                'Taco',
                'Brownie',
                'Potato',
                'Pasta',
                'Sand',
                'Burlap',
                'Diamond',
                'Laser',
                'Grenade',
                'Spaceship',
                'Light Saber',
                'Rapier',
                'Martini',
                'Whiskey',
                'Sausage',
                'Pirate',
                'Wizard',
                'Samurai',
                'Rascal',
                'Toad',
                'Cheesecake',
                'Cashew',
                'Orange',
                'Chocolate',
                'Chocolate Milk',
                'Hot Cocoa',
                'Bad Guy',
                'Knight',
                'Armor',
                'Cheetah',
                'Dragon',
                'Lizard',
                'Kraken',
                'Reactor',
                'Mafia',
                'Working Group',
                'Gang',
                'Force',
                'Agency',
                'Circle',
                'Society',
                'High Council',
                'Network',
                'Zone',
                'Arena',
                'Campfire',
                'Chamber',
                'Table',
                'Booth',
                'Dungeon',
                'Throne',
                'Tower',
                'Organization',
                'Coalition',
                );
                
            $this->event = array(
                'Blast-a-thon',
                'Feed',
                'Blowout',
                'Party',
                'Extravaganza',
                'Fiesta',
                'Hangout',
                'Snack-o-rama',
                'Ball',
                'Fest',
                'Blitz',
                'Scramble',
                'Convention',
                'Bake',
                'Mob',
                'Game',
                'Competition',
                'Seminar',);
        }
                
    }
}
