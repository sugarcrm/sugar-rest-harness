<?php

namespace SugarRestHarness\Randomizers;

class RandomizerFilteredBean extends RandomizerBean implements RandomizerInterface
{
    public $filters = [];

    public function getRandomData($params)
    {
        if (!isset($params['filters']) || empty($params['filters'])) {
            throw new \SugarRestHarness\RandomDataParamMissing(get_class($this), 'filters');
        }

        $this->filters = $params['filters'];

        return parent::getRandomData($params);
    }

    /**
     * sendListRequest()
     *
     * Sends a REST request using the harness itself to a LIST endpoint. This
     * endpoint should return a JSON array with a 'records' field, which will be
     * a nested array of bean data. This method returns that 'records' array.
     *
     * @param string $module - the name of a sugar module.
     * @return array - an array of nested sugar bean data.
     */
    public function sendListRequest($module)
    {
        $config = array(
            'method' => 'GET',
            'module' => $module,
            'routeMap' => 'list',
            'qs' => array(
                'fields' => implode(',', $this->field),
            ),
        );

        foreach ($this->filters as $filter => $searchTerm) {
            $config['qs'][$filter] = $searchTerm;
        }

        $job = new \SugarRestHarness\Jobs\Generic($config);
        $job->rawResults = $job->connector->makeRequest();
        $results = json_decode($job->rawResults, true);
        return $results['records'];
    }
}
