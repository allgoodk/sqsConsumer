<?php


namespace App\Queue\Sqs;


use App\Queue\Sqs\Enum\SqsParamsFields;
use Symfony\Component\Process\Process;

/**
 * Класс процесса, но с хранилкой сообщения, которое он обрабатывал, для дальнейшего взаимодействия с сообщением
 * например удалением этого сообщения.
 */
class SqsProcess extends Process
{
    /**
     * @var array
     */
    private $message;

    public function __construct(array $processData, array $sqsMessage)
    {
        $this->message = $sqsMessage;

        parent::__construct($processData);
    }

    /**
     * @return array
     */
    public function getMessage(): array
    {
        return $this->message;
    }

    /**
     * @return string|null
     */
    public function getMessageReceiptHandle(): ?string
    {
        return $this->message[SqsParamsFields::RECEIPT_HANDLE] ?? null;
    }
}