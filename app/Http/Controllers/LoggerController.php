<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerController extends Controller {
    private $logger;
    public function __construct($machine){
        $output = "[%datetime%] %channel%.%level_name%: %message% \n";
        $formatter = new LineFormatter($output);

        $streamHandler = new StreamHandler(storage_path('logs/'.date('Y-m-d').' - '.$machine->sap_code.' ('.$machine->name.').log'));
        $streamHandler->setFormatter($formatter);

        $this->logger = new Logger($machine->sap_code.' ('.$machine->name.')');
        $this->logger->pushHandler($streamHandler, Logger::INFO);
    }

    public function log($message, $machine){
        $this->logger->info($message);
    }
}
