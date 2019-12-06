<?php


namespace App\Queue\Sqs\Exception;


use InvalidArgumentException;

class RequiredArgumentException extends InvalidArgumentException
{
    private const REQUIRED_PARAM_ERROR_MESSAGE = 'Отсутсвует обязательный параметр:';

    /**
     * {@inheritDoc}
     */
    public function __construct($message = '', $code = 0)
    {
        parent::__construct(self::REQUIRED_PARAM_ERROR_MESSAGE . $message, $code);
    }
}