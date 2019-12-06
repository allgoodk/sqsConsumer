<?php


namespace App\Queue\Sqs\SqsParamsDto;


use App\Queue\Sqs\Enum\SqsParamsFields;
use App\Queue\Sqs\Exception\RequiredArgumentException;

class SendMessageParams
{
    /**
     * @var array
     */
    private $params;

    public function __construct(array $paramList)
    {
        if (false === array_key_exists(SqsParamsFields::QUEUE_URL, $paramList)) {
            throw new RequiredArgumentException(SqsParamsFields::QUEUE_URL);
        }

        if (false === array_key_exists(SqsParamsFields::MESSAGE_BODY, $paramList)) {
            throw new RequiredArgumentException(SqsParamsFields::MESSAGE_BODY);
        }

        $this->params = $paramList;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}