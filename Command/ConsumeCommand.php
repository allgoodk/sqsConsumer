<?php


namespace App\Queue\Sqs\Command;


use App\Queue\Sqs\Client\SqsClient;
use App\Queue\Sqs\Enum\SqsParamsFields;
use App\Queue\Sqs\SqsParamsDto\DeleteMessageParams;
use App\Queue\Sqs\SqsParamsDto\ReceiveMessageParams;
use App\Queue\Sqs\SqsProcess;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class ConsumeCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Аргумент "имя хэндлера для обработки сообщения"
     */
    private const HANDLER = 'handler';

    /**
     * Аргумент "имя очереди для получения сообщения"
     */
    private const QUEUE_URL = 'queue-url';

    /**
     * Колиечство хэндлеров, запущенных в процессе по умолчанию.
     */
    private const DEFAULT_WORKERS_COUNT = 1;

    /**
     * Аргумент команды отвечающий за количество запущеных процессов с воркерами внутри
     */
    private const MAXIMUM_WORKERS_COUNT = 'workers-count';

    /**
     * Имя команды, которую нужно запустить в процессе
     */
    private const SQS_HANDLE_MESSAGE_COMMAND_NAME = 'sqs:handle-message';

    /**
     * Сколько ожидаем наполнения очереди, если она пустая и тольько тогда возщвращаем пустой массив
     */
    private const DEFAULT_WAIT_TIME_IN_SECONDS = 20;

    /**
     * Путь где лежит конгсольный выполнятор
     */
    private const CONSOLE_COMMAND = 'bin/console';

    /**
     * Имя команды
     *
     * @var string
     */
    protected static $defaultName = 'sqs:receive';

    /**
     * @var SqsClient
     */
    private $client;

    /**
     * Массив с запущенными процессами
     *
     * @var SqsProcess[]
     */
    private $runningProcess = [];

    /**
     * Путь до бинарника PHP
     *
     * @var string
     */
    private $phpBinaryPath;

    /**
     * @var string
     */
    private $queueUrl;

    /**
     * ConsumeCommand constructor.
     *
     * @param SqsClient $client
     */
    public function __construct(SqsClient $client)
    {
        $this->client = $client;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Receives message from sqs')
            ->addArgument(self::QUEUE_URL, InputArgument::REQUIRED, 'Queue url')
            ->addArgument(self::HANDLER, InputArgument::REQUIRED, 'Handler FQCN,
             used for handling received message')
            ->addArgument(self::MAXIMUM_WORKERS_COUNT, InputArgument::OPTIONAL, 'Maximum count 
             of handlers for handling messages', self::DEFAULT_WORKERS_COUNT)
            ->setHelp('Receives message from specified in command args queue and sends it to specified processor');

    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //TODO gracefull shutdown
        $this->queueUrl       = $input->getArgument(self::QUEUE_URL);
        $receiveMessageParams = $this->getReceiveMessageParams($input);
        $handler              = $input->getArgument(self::HANDLER);

        while (empty($this->runningProcess)) {
            $executionStartTime = microtime(true);
            $messagesList       = $this->client->receiveMessage($receiveMessageParams);
            foreach ($messagesList as $message) {
                $this->startProcess($message, $handler);
            }

            $this->handleProcessList();
            $executionEndTime = microtime(true);
            //The result will be in seconds and milliseconds.
            $seconds = $executionEndTime - $executionStartTime;
            $output->writeln('время выполнения  ' . $seconds);
            $output->writeln('память  выполнения  ' . $this->convert(memory_get_usage(true)));
        }

    }

    /**
     * @param array  $message
     * @param string $handler
     */
    private function startProcess(array $message, string $handler): void
    {
        if (empty($message[SqsParamsFields::BODY])) {
            throw new InvalidArgumentException('Неправильные данные в сообщение от SQS');
        }
        $process = new SqsProcess(
            [
                $this->getPhpBinaryPath(),
                self::CONSOLE_COMMAND,
                self::SQS_HANDLE_MESSAGE_COMMAND_NAME,
                $handler,
                $message[SqsParamsFields::BODY],
            ],
            $message
        );

        $process->start();

        // добавляем в пулл запущенных процессов
        $this->runningProcess[] = $process;
    }

    /**
     * @return string
     */
    private function getPhpBinaryPath(): string
    {
        if ($this->phpBinaryPath === null) {
            $phpBinaryFinder     = new PhpExecutableFinder();
            $this->phpBinaryPath = $phpBinaryFinder->find();
        }

        return $this->phpBinaryPath;
    }

    /**
     * @param int $size
     *
     * @return string
     */
    private function convert($size)
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        return @round($size / (1024 ** ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[(int)$i];
    }

    /**
     * @param InputInterface $input
     * @return ReceiveMessageParams
     */
    private function getReceiveMessageParams(InputInterface $input): ReceiveMessageParams
    {
        $receiveParamList = [
            SqsParamsFields::QUEUE_URL              => $this->queueUrl,
            SqsParamsFields::WAIT_TIME_SECONDS      => self::DEFAULT_WAIT_TIME_IN_SECONDS,
            // этот параметр управляет количество полученных сообщений, т.к на каждое сообщение запускается по процессу,
            // то получается, что макисмальное количество получаемых сообщений управляет максимальным количеством
            // запущенных процессов
            SqsParamsFields::MAX_NUMBER_OF_MESSAGES => $input->getArgument(self::MAXIMUM_WORKERS_COUNT),
        ];

        return new ReceiveMessageParams($receiveParamList);
    }

    /**
     * Обрабатываем каждый процесс и получаем результат.
     */
    private function handleProcessList(): void
    {
        while ($process = array_shift($this->runningProcess)) {
            // Если процесс не завершился - вертаем обратно в очередь
            if ($process->getStatus() !== Process::STATUS_TERMINATED) {
                $this->runningProcess[] = $process;
                continue;
            }

            switch ($process->getExitCode()) {
                case 1:
                    var_dump(1);
                    $this->logger->error($process->getErrorOutput());
                    break;
                case 0:
                    var_dump(0);

                    $this->client->deleteMessage(
                        new DeleteMessageParams(
                            [
                                SqsParamsFields::RECEIPT_HANDLE => $process->getMessageReceiptHandle(),
                                SqsParamsFields::QUEUE_URL      => $this->queueUrl,
                            ]
                        )
                    );
                    break;
                case 2:
                default:
                    // Крайне нештатная ситуация , логгируем и не даем запускаться основной команде
                    $this->logger->critical('выполнение команды невозможно', [$process->getCommandLine()]);
                    throw new RuntimeException('Выполнение команды невозможно');
            }
        }
    }
}