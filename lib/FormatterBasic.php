<?php
namespace SugarRestHarness;

/**
 * FormatterBasic
 *
 * This formatter only implements the basic behavior found in FormatterBase.
 *
 * @see FormatterBase
 */
class FormatterBasic extends FormatterBase implements FormatterInterface
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
