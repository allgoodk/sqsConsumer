<?php


namespace App\Queue\Sqs\SqsParamsDto;


use App\Queue\Sqs\Enum\SqsParamsFields;
use App\Queue\Sqs\Exception\RequiredArgumentException;
use InvalidArgumentException;
use Renlife\ApiTools\DTO\AbstractDto;

/**
 * ДТО позволяющее комфортно работать с получением сообщений из очереди.
 */
class ReceiveMessageParams
{
    /**
     * Все доступные параметры для получения сообщения
     */
    public const AVAILABLE_PARAMS = [
        SqsParamsFields::QUEUE_URL,
        SqsParamsFields::MAX_NUMBER_OF_MESSAGES,
        SqsParamsFields::MESSAGE_ATTRIBUTE_NAME,
        SqsParamsFields::RECEIVE_REQUEST_ATTEMPT_ID,
        SqsParamsFields::VISIBILITY_TIMEOUT,
        SqsParamsFields::WAIT_TIME_SECONDS,
    ];

    /**
     * Текст ошибики передачи несуществующего параметра
     */
    private const WRONG_PARAM_ERROR_MESSAGE = 'Передан неверный пкараметр: ';

    /**
     * @var array
     */
    private $params;

    /**
     * ReceiveMessageParams constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        if (false === array_key_exists(SqsParamsFields::QUEUE_URL, $params)) {
            throw new RequiredArgumentException(SqsParamsFields::QUEUE_URL);
        }

        $this->validateParams($params);

        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    private function validateParams(array $params): void
    {
        foreach (array_keys($params) as $key) {
            if (false === in_array($key, self::AVAILABLE_PARAMS, true)) {
                throw new InvalidArgumentException(self::WRONG_PARAM_ERROR_MESSAGE . $key);
            }
        }
    }
}