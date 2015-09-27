<?php
namespace SugarRestHarness\Randomizers;

class RandomizerPhoneNumber extends RandomizerAbstract implements RandomizerInterface
{
    /** 
     * getRandomData()
     *
     * Gets a random phone number. If a pattern is passed in, it will use that
     * pattern and replace all of the 'd' character in the patter with a random
     * number. Default pattern is ddd-ddd-dddd (US phone number).
     *
     * @param array $params - a hash of optional params. 'pattern' is supported.
     * @return string - a random phone number.
     */
    public function getRandomData($params)
    {
        $phoneNumber = '';
        $pattern = !empty($params['pattern']) ? $params['pattern'] : 'ddd-ddd-dddd';
        $chars = preg_split("//", $pattern);
        foreach ($chars as $char) {
            if ($char == 'd') {
                $phoneNumber .= rand(0, 9);
            } else {
                $phoneNumber .= $char;
            }
        }
        return $phoneNumber;
    }   
}
