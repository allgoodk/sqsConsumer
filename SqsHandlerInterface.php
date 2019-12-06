<?php


namespace App\Queue\Sqs;


interface SqsHandlerInterface
{
    /**
     * @param int $entityId
     */
   // public function handle(int $entityId): void;

    /**
     * Возвращает имя ключа, по которому будет индексироваться в коллекции хэндлеров данный хэндлер.
     * @see https://symfony.com/blog/new-in-symfony-4-3-indexed-and-tagged-service-collections
     *
     * @return string
     */
    public static function getDefaultKeyName(): string;

    /**
     * Метод, возвращает класс сущности, с которой работает
     *
     * @return string
     */
    public static function getHandlingClassname(): string;
}