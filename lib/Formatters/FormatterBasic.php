<?php
/*
 * Copyright (c) 2015 SugarCRM Inc. Licensed by SugarCRM under the Apache 2.0 license.
 */
namespace SugarRestHarness\Formatters;

/**
 * FormatterBasic
 *
 * This formatter only implements the basic behavior found in FormatterBase.
 *
 * @see FormatterBase
 */
class FormatterBasic extends \SugarRestHarness\Formatters\FormatterBase implements \SugarRestHarness\Formatters\FormatterInterface
{
    /**
     * format()
     *
     * Formats the results from all jobs in the repository. See the FormatterBase
     * file for details.
     *
     * @see FormatterBase
     * @return string - formatted results.
     */
    public function format()
    {
        return parent::format();
    }
}
