<?php
namespace SugarRestHarness\Jobs\Examples\Metadata;

class Metadata extends \SugarRestHarness\JobAbstract implements \SugarRestHarness\JobInterface
{
    public function __construct($options)
    {
        $this->config['routeMap'] = 'allMetadata';
        $this->config['module'] = 'Metadata';
        $this->config['type_filter'] = 'modules';
        $this->config['module_filter'] = 'Tasks';
        $this->config['platform'] = 'mobile';
        parent::__construct($options);
    }
}

/*
/rest/v10/metadata?type_filter=modules&module_filter=Tasks&platform=mobile
*/
