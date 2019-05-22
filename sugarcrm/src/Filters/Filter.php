<?php declare(strict_types=1);
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Filters;

use ServiceBase;
use SugarApiExceptionInvalidParameter;
use SugarApiExceptionRequestMethodFailure;
use Sugarcrm\Sugarcrm\Filters\Field\EmailParticipants as EmailParticipantsField;
use Sugarcrm\Sugarcrm\Filters\Field\Field;
use Sugarcrm\Sugarcrm\Filters\Operand\EmailParticipants as EmailParticipantsOperand;
use Sugarcrm\Sugarcrm\Filters\Operand\Operand;

/**
 * Formats or unformats a complete filter definition.
 */
final class Filter implements Serializable
{
    /**
     * The API controller.
     *
     * @var ServiceBase
     */
    private $api;

    /**
     * The filter definition.
     *
     * @var array
     */
    private $filter;

    /**
     * `format` or `unformat`. Allows code for walking the filter to be reused for
     * both formatting and unformatting. The mode is used to determine which method
     * to call on a {@link Serializable} object.
     *
     * @var string
     */
    private $mode;

    /**
     * The module in which the filter definition is used.
     *
     * @var string
     */
    private $module;

    /**
     * Constructor.
     *
     * @param ServiceBase $api Provides the API context.
     * @param string $module The module in which the filter definition is used.
     * @param array $filter A complete filter definition.
     */
    public function __construct(ServiceBase $api, string $module, array $filter)
    {
        $this->api = $api;
        $this->module = $module;
        $this->filter = $filter;
    }

    /**
     * Walks the filter definition, formatting each segment, and returns the filter
     * definition formatted for the API client.
     *
     * @return array
     */
    public function format()
    {
        // Enter `format` mode before walking the filter and exit the mode when
        // finished.
        $this->mode = 'format';
        $filter = $this->doFilters($this->filter);
        unset($this->mode);

        return $filter;
    }

    /**
     * Walks the filter definition, unformatting each segment, and returns the filter
     * definition unformatted for the database.
     *
     * @return array
     */
    public function unformat()
    {
        // Enter `unformat` mode before walking the filter and exit the mode when
        // finished.
        $this->mode = 'unformat';
        $filter = $this->doFilters($this->filter);
        unset($this->mode);

        return $filter;
    }

    /**
     * Walks the filter definition and applies the mode's command(s) to each child
     * segment.
     *
     * @param array $filters The filter defintion to walk.
     *
     * @return array
     * @throws SugarApiExceptionInvalidParameter
     * @throws \SugarApiException The operand and field implementations throw
     * instances of {@link \SugarApiException} implementations.
     */
    private function doFilters(array $filters) : array
    {
        foreach ($filters as $i => $filter) {
            if (!is_array($filter)) {
                throw new SugarApiExceptionInvalidParameter(
                    sprintf(
                        'Did not recognize the definition: %s',
                        print_r($filter, true)
                    )
                );
            }

            foreach ($filter as $operand => $value) {
                $filters[$i][$operand] = $this->doFilter($operand, $value);
            }
        }

        return $filters;
    }

    /**
     * Applies the mode's command(s) to a segment of a filter definition.
     *
     * @param string $operand The operand or field name to which the filter belongs.
     * @param mixed $filter The filter definition under the operand or field name.
     *
     * @return mixed The filter definition resulting from the application of the
     * mode's command(s).
     * @throws \SugarApiException The operand and field implementations throw
     * instances of {@link \SugarApiException} implementations.
     */
    private function doFilter(string $operand, $filter)
    {
        switch ($operand) {
            case '$or':
            case '$and':
                return $this->doFilters($filter);
            case '$creator':
            case '$favorite':
            case '$following':
            case '$owner':
            case '$tracker':
                return $this->doOperand(new Operand($this->api, $operand, $filter));
            case '$from':
            case '$to':
            case '$cc':
            case '$bcc':
                return $this->doOperand(
                    new EmailParticipantsOperand($this->api, $operand, $filter)
                );
            default:
                return $this->doField($operand, $filter);
        }
    }

    /**
     * Applies the mode's command(s) to a field segement of a filter definition.
     *
     * @param string $field The field name.
     * @param mixed $filter The field segment of a filter definition.
     *
     * @return mixed The filter definition resulting from the application of the
     * mode's command(s).
     * @throws SugarApiExceptionInvalidParameter
     * @throws \SugarApiException The operand and field implementations throw
     * instances of {@link \SugarApiException} implementations.
     */
    private function doField(string $field, $filter)
    {
        $epFields = [
            'from_collection',
            'to_collection',
            'cc_collection',
            'bcc_collection',
        ];
        $isAnEmailParticipantsField = false;

        if ($this->module === 'Emails' && in_array($field, $epFields)) {
            $isAnEmailParticipantsField = true;
        }

        if ($isAnEmailParticipantsField) {
            if (!is_array($filter)) {
                throw new SugarApiExceptionInvalidParameter("{$field} requires an array");
            }

            $operand = new EmailParticipantsField($this->api, $field, $filter);
        } else {
            $operand = new Field($this->api, $field, $filter);
        }

        return $this->doOperand($operand);
    }

    /**
     * Applies the mode's command(s) to a {@link Serializable} object.
     *
     * @param Serializable $operand The operand or field to act upon.
     *
     * @return mixed The filter definition resulting from the application of the
     * mode's command(s).
     * @throws SugarApiExceptionRequestMethodFailure If the mode is unknown.
     * @throws \SugarApiException The {@link Serializable} implementations throw
     * instances of {@link \SugarApiException} implementations.
     */
    private function doOperand(Serializable $operand)
    {
        switch ($this->mode) {
            case 'format':
                return $operand->format();
            case 'unformat':
                return $operand->unformat();
            default:
                throw new SugarApiExceptionRequestMethodFailure(
                    sprintf('Unable to process filter: %s', print_r($this->filter, true))
                );
        }
    }
}
