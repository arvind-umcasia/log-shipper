<?php

namespace ArvindUmcasia\LogShipper\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class LogShipperShipCommand extends Command
{
    protected $signature = 'log-shipper:ship';
    protected $description = 'Ship logs to a remote server.';

    public function handle()
    {
        $todayDate = now()->format('Y-m-d');

        $directoryPath = storage_path('logs/log-shipper/' . $todayDate);

        $files = File::files($directoryPath);

        $logLevelMap = [
            'error' => 'error',
            'warning' => 'warning',
            'debug' => 'debug',
            'emergency' => 'emergency',
            'info' => 'info'
        ];

        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);

            if (isset($logLevelMap[$filename])) {
                $logLevel = $logLevelMap[$filename];
                $this->shipToLogView($file, $logLevel);
            }
        }

        $this->warn('Logs shipped!');
    }

    private function shipToLogView($logFilePath, $logLevel)
    {
        $flaskEndpoint = env('LOGVIEW_ENDPOINT') . $logLevel;

        $response = Http::attach(
            'logs',
            file_get_contents($logFilePath),
            basename($logFilePath)
        )->post($flaskEndpoint);

        if ($response->successful()) {
            $this->info($logFilePath . " file sent successfully to logview.");
        } else {
            $this->error("Failed to send log file to logview. Status code: " . $response);
        }
    }
}
