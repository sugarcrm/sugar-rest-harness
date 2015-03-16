<?php
namespace SugarRestHarness\Jobs\Examples\Metadata;

class Metadata extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'allMetadata';
        $this->config['module'] = 'Metadata';
        $this->config['qs']['type_filter'] = 'modules';
        $this->config['qs']['module_filter'] = 'Tasks';
        $this->config['qs']['platform'] = 'mobile';
        parent::__construct($options);
    }
}

/*
/rest/v10/metadata?type_filter=modules&module_filter=Tasks&platform=mobile
*/
