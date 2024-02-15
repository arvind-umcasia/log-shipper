<?php

namespace ArvindUmcasia\LogShipper\Logging;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;

class LogShipLogger extends MonologLogger
{
    public function __construct($name, array $handlers = [], array $processors = [])
    {
        parent::__construct($name, $handlers, $processors);
    }

    public function log($level, $message, array $context = []): void
    {
        // Get caller information
        $callerInfo = $this->getCallerInfo();

        // Get request information
        $requestInfo = $this->getRequestInfo();

        // Generate a unique request ID
        $requestId = Str::uuid()->toString();

        // Merge caller, request, and request ID information with context
        $context = array_merge($context, $callerInfo, $requestInfo, ['request_id' => $requestId]);

        // Determine log file based on log level
        $logFile = $this->getLogFile($level);

        // Add StreamHandler for the determined log file
        $this->pushHandler(new StreamHandler($logFile, $level));

        // Log the message
        parent::log($level, $message, $context);
    }

    private function getCallerInfo(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        return [
            'caller' => $trace[1]['function'], // Method name of the caller
            'file' => $trace[1]['file'], // File where the log is being called
            'line' => $trace[1]['line'], // Line number where the log is being called
        ];
    }

    private function getRequestInfo(): array
    {
        $request = Request::instance();

        return [
            'request_method' => $request->method(), // Request method
            'request_url' => $request->fullUrl(), // Full URL
            'request_headers' => $request->header(), // Request headers
            'request_payload' => $request->all(), // Request payload
            'ip_address' => $request->ip(), // IP Address
            'user_agent' => $request->userAgent(), // User Agent
            'user_id' => auth()->id(), // User ID (if authenticated)
            'session_id' => session()->getId(), // Session ID (if session is used)
        ];
    }

    private function getLogFile($level): string
    {
        $logDirectory = storage_path('logs/log-shipper/' . now()->format('Y-m-d')); // Use today's date as directory

        if (!is_dir($logDirectory)) {
            mkdir($logDirectory, 0755, true); // Create directory if it doesn't exist
        }
    
        switch ($level) {
            case MonologLogger::DEBUG:
                return $logDirectory . '/debug.log';
            case MonologLogger::INFO:
                return $logDirectory . '/info.log';
            case MonologLogger::NOTICE:
                return $logDirectory . '/notice.log';
            case MonologLogger::WARNING:
                return $logDirectory . '/warning.log';
            case MonologLogger::ERROR:
                return $logDirectory . '/error.log';
            case MonologLogger::CRITICAL:
                return $logDirectory . '/critical.log';
            case MonologLogger::ALERT:
                return $logDirectory . '/alert.log';
            case MonologLogger::EMERGENCY:
                return $logDirectory . '/emergency.log';
            default:
                return $logDirectory . '/debug.log';
        }
    }
}
