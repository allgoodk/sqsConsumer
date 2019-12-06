<?php


namespace App\Queue\Sqs\SqsParamsDto;


use App\Queue\Sqs\Enum\SqsParamsFields;
use App\Queue\Sqs\Exception\RequiredArgumentException;

class DeleteMessageParams
{
    /**
     * @var array
     */
    private $params;

    /**
     * DeleteMessageParams constructor.
     *
     * @param array $paramList
     */
    public function __construct(array $paramList)
    {
        if (false === array_key_exists(SqsParamsFields::QUEUE_URL, $paramList)) {
            throw new RequiredArgumentException(SqsParamsFields::QUEUE_URL);
        }

        if (false === array_key_exists(SqsParamsFields::RECEIPT_HANDLE, $paramList)) {
            throw new RequiredArgumentException(SqsParamsFields::RECEIPT_HANDLE);
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