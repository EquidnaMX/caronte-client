<?php

/**
 * Command to notify the Caronte server of the current configuration and available roles.
 *
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.3.2
 */

namespace Equidna\Caronte\Console\Commands;

use Illuminate\Console\Command;
use Equidna\Caronte\CaronteRequest;
use Exception;

class NotifyClientConfigurationCommand extends Command
{
    protected $signature    = 'caronte:notify-client-configuration';
    protected $description  = 'Notify Caronte server current configuration and available Roles';

    /**
     * Execute the console command to notify the Caronte server of configuration and roles.
     *
     * @return int Exit code
     */
    public function handle(): int
    {
        $this->line('Notifying Caronte server current configuration and available Roles');
        try {
            $this->line(CaronteRequest::notifyClientConfiguration());
            $this->info('Notifying Caronte server configuration done!!');
            return 0;
        } catch (Exception $e) {
            $this->error('Error notifying Caronte server configuration');
            $this->error($e->getMessage());
            return 1;
        }
    }
}
