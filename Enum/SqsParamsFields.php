<?php


namespace App\Queue\Sqs\Enum;

/**
 * Справочник возможных полей реквеста и респонса SQS
 */
class SqsParamsFields
{
    public const MESSAGE = 'message';
    public const BODY = 'Body';
    public const MESSAGES = 'Messages';
    /**
     * Идентификатор сообщения для удлаения, обязательный
     */
    public const RECEIPT_HANDLE = 'ReceiptHandle';
    /**
     * Урл очереди, обязательный
     */
    public const QUEUE_URL = 'QueueUrl';

    /**
     * Количество сообщений, которые надо забрать из очреди
     */
    public const MAX_NUMBER_OF_MESSAGES = 'MaxNumberOfMessages';

    /**
     * Аттрибут сообщения
     */
    public const MESSAGE_ATTRIBUTE_NAME = 'MessageAttributeName';

    /**
     * ИД попытки получить сообщение (для fifo очереди)
     */
    public const RECEIVE_REQUEST_ATTEMPT_ID = 'ReceiveRequestAttemptId';

    /**
     * Таймаут, на который необходимо скрыть сообщение от других консьюмеров из очереди
     * НЕ УДАЛИТЬ!
     */
    public const VISIBILITY_TIMEOUT = 'VisibilityTimeout';

    /**
     * Время ожидания поступления сообщения в очередь, если больше нуля, то реализован long-polling
     */
    public const WAIT_TIME_SECONDS = 'WaitTimeSeconds';

    /**
     * Тело сообщения для отправки
     */
    public const MESSAGE_BODY = 'MessageBody';

    /**
     * ID группы сообщений, обязательно в FIFO очередях
     */
    public const MESSAGE_GROUP_ID = 'MessageGroupId';
}