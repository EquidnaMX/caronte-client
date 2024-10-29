<?php

/**
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.1.0
 *
 * This command is used to update the Caronte server configuration and available roles for this application.
 * Roles are stored on the caronte-roles.php file and are updated on the Caronte server when this command is executed.
 * A valida APP_ID and APP_SECRET are required to execute this command.
 */

namespace Equidna\Caronte\Console\Commands;

use Illuminate\Console\Command;
use Equidna\Caronte\CaronteRequest;
use Exception;

class NotifyClientConfigurationCommand extends Command
{
    protected $signature    = 'caronte:notify-client-configuration';
    protected $description  = 'Notify Caronte server current configuration and available Roles';

    public function handle()
    {
        $this->line('Notifying Caronte server current configuration and available Roles');
        try {
            $this->line(CaronteRequest::notifyClientConfiguration());
            $this->info('Notifying Caronte server configuration done!!');
        } catch (Exception $e) {
            $this->error('Error notifying Caronte server configuration');
            $this->error($e->getMessage());
        }
    }
}
