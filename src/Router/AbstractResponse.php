<?php
/**
 * teqneers/ext-direct
 *
 * @category   TQ
 * @package    TQ\ExtDirect
 * @copyright  Copyright (C) 2015 by TEQneers GmbH & Co. KG
 */

namespace TQ\ExtDirect\Router;

/**
 * Class AbstractResponse
 *
 * @package TQ\ExtDirect
 */
class AbstractResponse implements \JsonSerializable
{
    const TYPE_RPC       = 'rpc';
    const TYPE_EXCEPTION = 'exception';
    const TYPE_EVENT     = 'event';

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array(
            'type' => $this->getType()
        );
    }
}
