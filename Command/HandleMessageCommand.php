<?php


namespace App\Queue\Sqs\Command;


use App\Queue\Sqs\SqsHandlerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Traversable;

class HandleMessageCommand extends Command
{
    /**
     * Имя аргумента команды, отвечающей за полученные данные для обработки
     */
    private const MESSAGE = 'message';

    /**
     * Имя аргумента команды, отвечающего за запускаемый хэндлер
     */
    private const HANDLER = 'handler';

    /**
     * Имя команды
     *
     * @var string
     */
    protected static $defaultName = 'sqs:handle-message';

    /**
     * Коллекция хэндлеров, имплементирующих интерфейс App\Queue\Sqs\SqsHandlerInterface
     *
     * @var SqsHandlerInterface[]
     */
    private $handlerCollections;

    /**
     * HandleMessageCommand constructor.
     *
     * @param Traversable $handlerCollections
     */
    public function __construct(iterable $handlerCollections)
    {
        $this->handlerCollections = iterator_to_array($handlerCollections);

        parent::__construct();

    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->addArgument(self::HANDLER, InputArgument::OPTIONAL);
        $this->addArgument(self::MESSAGE, InputArgument::OPTIONAL);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SqsHandlerInterface $handler */
        $handler = $this->handlerCollections[$input->getArgument(self::HANDLER)] ?? null;
        if (null === $handler) {
           exit(2);
        }
        $handler->handle((int)$input->getArgument(self::MESSAGE));
    }
}