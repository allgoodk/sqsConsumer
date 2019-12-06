<?php


namespace App\Queue\Sqs\Client;


use App\Queue\Sqs\Enum\SqsParamsFields;
use App\Queue\Sqs\SqsParamsDto\DeleteMessageParams;
use App\Queue\Sqs\SqsParamsDto\ReceiveMessageParams;
use App\Queue\Sqs\SqsParamsDto\SendMessageParams;
use Aws\AwsClientInterface;
use Aws\Result;

class SqsClient
{

    /**
     * @var AwsClientInterface
     */
    private $client;

    /**
     * SqsClient constructor.
     *
     * @param AwsClientInterface $client
     */
    public function __construct(AwsClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param SendMessageParams $sendMessageParams
     *
     * @return array
     */
    public function sendMessage(SendMessageParams $sendMessageParams): array
    {
        /** @var Result $res */
        $res = $this->client->sendMessage($sendMessageParams->getParams());

        return $res->toArray();
    }

    /**
     * @param ReceiveMessageParams|null $receiveMessageParams
     *
     * @return array
     */
    public function receiveMessage(ReceiveMessageParams $receiveMessageParams): array
    {
        /** @var Result $res */
        $res = $this->client->receiveMessage($receiveMessageParams->getParams());

        return $res->toArray()[SqsParamsFields::MESSAGES] ?? [];
    }

    /**
     * @param DeleteMessageParams $deleteMessageParams
     * @return array
     */
    public function deleteMessage(DeleteMessageParams $deleteMessageParams): array
    {
        /** @var Result $res */
        $res = $this->client->deleteMessage($deleteMessageParams->getParams());

        return $res->toArray();
    }
}