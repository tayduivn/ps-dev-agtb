<?php
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

namespace Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator;

use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;
use Sugarcrm\Sugarcrm\Security\Validator\Constraints\PhpSerialized;
use Sugarcrm\Sugarcrm\Security\Validator\Validator;
use Sugarcrm\Sugarcrm\Util\Serialized;

/**
 * Class PHPSerializeSafe
 * Prevent PHP objects injection.
 * @package JobQueue
 */
class PHPSerializeSafe extends AbstractDecorator
{
    /**
     * Save workload as raw data. Does not allow objects in data.
     * @throw InvalidArgumentException If objects found.
     * {@inheritdoc}
     */
    public function decorate($data, WorkloadInterface $workload)
    {
        if (!($data instanceof WorkloadInterface)) {
            throw new InvalidArgumentException('Invalid data in decorator. WorkloadInterface required.');
        }
        $serializedData = serialize(
            [
                'instance' => get_class($data),
                'route' => $data->getRoute(),
                'payload' => $data->getData(),
                'attributes' => $data->getAttributes(),
            ]
        );

        $violations = Validator::getService()->validate($serializedData, new PhpSerialized());
        if (count($violations) > 0) {
            throw array_reduce(iterator_to_array($violations), function ($exception, $violation) {
                return empty($exception) ?
                    new InvalidArgumentException($violation->getMessage()) :
                    new InvalidArgumentException($violation->getMessage(), 0, $exception);
            });
        }
        return parent::decorate($serializedData, $workload);
    }

    /**
     * Restore workload from data.
     * @throw InvalidArgumentException If objects found.
     * {@inheritdoc}
     */
    public function undecorate($data)
    {
        $unserializedData = Serialized::unserialize(parent::undecorate($data));
        if (!$unserializedData) {
            throw new InvalidArgumentException('Invalid payload. Objects are not allowed.');
        }
        /**
         * @var string $instance Class name.
         * @var string $route
         * @var string $payload
         * @var string $attributes
         */
        extract($unserializedData);

        return new $instance($route, $payload, $attributes);
    }
}
