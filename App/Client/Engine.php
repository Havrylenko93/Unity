<?php

namespace App\Client;

class Engine
{
    /**
     * @var string $ip
     */
    private $ip;

    /**
     * @var int $port
     */
    private $port;

    /**
     * Socket
     * @var mixed $socket
     */
    private $socket;

    /**
     * @var BotInterface
     */
    private $bot;

    /**
     * @var string
     */
    private $botClass;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Client
     */
    private $byteLength;

    /**
     * @var resource
     */
    private $shm;

    /**
     * @var string
     */
    private $gotSignal = false;

    /**
     * Client constructor.
     *
     * @param string $ip
     * @param int $port
     * @param string $botClass
     */
    public function __construct(string $ip, int $port, string $botClass)
    {
        if (file_exists('log')) {
            unlink('log');
        }
        $this->port = $port;
        $this->ip = $ip;
        $this->byteLength = 1024 * 100;
        $this->botClass = $botClass;
        $this->shm = shmop_open(0, "n", 0660, $this->byteLength);
    }

    public function run()
    {
        if (!$this->socket) {
            $this->socketConnect();
            $this->fork();
        }
    }

    private function socketConnect()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($this->socket, $this->ip, $this->port);
        socket_set_block($this->socket);
    }

    private function fork()
    {
        pcntl_async_signals(true);
        $parentPid = getmypid();
        $pid = pcntl_fork();
        if ($pid == -1) {
            exit(1);
        } else {
            if ($pid) {
                $this->client = new Client($this);
                $this->bot = new $this->botClass;
                pcntl_signal(SIGALRM, [$this, 'turnHandler']);
                pcntl_signal(SIGUSR1, [$this, 'stopHandler']);
                $this->waitSignal();
            } else {
                pcntl_signal(SIGTERM, function () {
                    exit(0);
                });
                usleep(5);
                $this->socketRead($parentPid);
            }
        }
    }

    private function waitSignal()
    {
        try {
            while (true) {
                if ($this->gotSignal) {
                    $this->turn();
                    $this->gotSignal = false;
                } else {
                    usleep(1);
                }
            }
        } catch (TimeoutException $e) {
            $this->gotSignal = true;
            $this->waitSignal();
        }
    }

    private function turn()
    {
        $data = $this->getGameData();
        $str = mb_strcut($data, 2, null, 'UTF-8');
        $arr = explode(':', $str);
        if ($arr) {
            $this->client->parseData($arr);
            $this->bot->turn($this->client, $this->client->turnNumber);
        }
    }

    private function getGameData()
    {
        $data = shmop_read($this->shm, 0, shmop_size($this->shm));

        return trim($data);
    }

    private function socketRead($parentPid)
    {
        while ($out = socket_read($this->socket, $this->byteLength)) {
            if ($out) {
                if (substr($out, -4) === 'stop') {
                    socket_shutdown($this->socket, 2);
                    socket_close($this->socket);
                    shmop_delete($this->shm);
                    shmop_close($this->shm);
                    posix_kill($parentPid, SIGUSR1);
                    exit(0);
                } else {
                    $startTime = microtime(true);
                    $this->setGameData($out . ';' . $startTime);
                    posix_kill($parentPid, SIGALRM);
                }
            }
        }
    }

    private function setGameData($str)
    {
        shmop_write($this->shm, str_pad($str, shmop_size($this->shm), ' ', STR_PAD_RIGHT), 0);
    }

    public function socketWrite(string $message)
    {
        $len = strlen($message);
        $message = chr(0xff & ($len >> 8)) . chr(0xff & $len) . $message;
        socket_write($this->socket, $message, strlen($message));
    }

    /**
     * @throws TimeoutException
     */
    public function turnHandler()
    {
        if ($this->gotSignal) {
            throw new TimeoutException;
        } else {
            $this->gotSignal = true;
        }
    }

    public function stopHandler()
    {
        pcntl_wait($status);
        exit(0);
    }

    public static function init($botClass)
    {
        if (empty($botClass)) {
            echo 'Choose bot name';
            exit;
        }

        if (!class_exists($botClass)) {
            echo 'Bot with name: ' . $botClass . ' not exists';
            exit;
        }

        global $argv;

        $ip = $argv[1] ?? '127.0.0.1';
        $port = $argv[2] ?? 15000;
        (new self((string)$ip, (int)$port, $botClass))->run();
    }

    private function log($msg, $file = 'log')
    {
        file_put_contents($file, getmypid() . ':' . $msg . PHP_EOL . PHP_EOL, FILE_APPEND);
    }

}