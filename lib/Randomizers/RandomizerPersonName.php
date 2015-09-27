<?php
namespace SugarRestHarness\Randomizers;

class RandomizerPersonName extends RandomizerAbstract implements RandomizerInterface
{
    public $firstNames = array();
    public $lastNames = array();
    
    /**
     * getRandomData()
     *
     * Returns a random name. $params must specify 'type' = 'first|last'.
     *
     * @param array $params - a hash of parameters. Must include 'type' => 'first|last'.
     * @return string - a randomly selected name.
     */
    public function getRandomData($params)
    {
        if (!isset($params['type'])) {
            throw new RandomDataParamMissing(get_class($this), 'type');
        }
        
        $type = $params['type'] . 'Names';
        if (empty($this->$type)) {
            $this->populate();
        }
        $list = $this->$type;
        return $list[rand(0, count($list) - 1)];
    }
    
    
    /**
     * populate()
     *
     * Populates the lists of first and last names.
     */
    public function populate()
    {
        $this->firstNames = array(
        'Albert',
        'Bert',
        'Charlie',
        'Daniel',
        'Edward',
        'Frank',
        'Greg',
        'Hank',
        'Ignatz',
        'John',
        'Kim',
        'Mark',
        'Ned',
        'Oliver',
        'Peter',
        'Quinn',
        'Ralph',
        'Samuel',
        'Trevor',
        'Ulysses',
        'Victor',
        'Webster',
        'Xavier',
        'York',
        'Zennon',
        'Alice',
        'Betty ',
        'Charlene',
        'Darlene',
        'Edith',
        'Francesca',
        'Gwendolyn',
        'Harmony',
        'Isabella',
        'Jessamin',
        'Kim',
        'Laura',
        'Marcie',
        'Natasha',
        'Olivia',
        'Petra',
        'Quianna',
        'Rachel',
        'Stephanie',
        'Trisha',
        'Uma',
        'Valerie',
        'Wanda',
        'Xena',
        'Yesinia',
        'Zoey'
        );
        
        $this->lastNames = array(
        'Albertsen',
        'Bensen',
        'Carlsen',
        'Davidsen',
        'Edmonsen',
        'Fredricksen',
        'Gundersen',
        'Hendersen',
        'Ivarsen',
        'Jarlsen',
        'Kindersen',
        'Larsen',
        'Michaelsen',
        'Nicholsen',
        'Olesen',
        'Petersen',
        'Quirckesen',
        'Robertsen',
        'Stephensen',
        'Tennisen',
        'Ulfssen',
        'Vernersen',
        'Waltersen',
        'Xon',
        'Yonkersen',
        'Zellwegersen',
        );
    }
}
