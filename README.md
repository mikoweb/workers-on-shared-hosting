# Workers on Shared Hosting

The application allows to start, maintain, restart and stop workers on Shared Hosting. 
This is a solution in case there is no access to Supervisor, Systemd, etc.

You can use `cron` to maintain workers.

## Installation

Clone the repo and run it:

    composer install

Requires at least PHP 8.3 and composer.

## Defining workers

Create `workers.yaml` file:

```yaml
-
    name: AppWorker
    instances_number: 10
    working_directory: '/path/to/your/application'
    command: ['php8.3', 'bin/console', 'messenger:consume', 'async']
-
    name: AnotherWorker
    instances_number: 1
    working_directory: '/path/to/your/application'
    command: ['python', 'worker_script.py']
```

## Command Line

To start single worker:

    bin/console app:worker:start WorkerName

To start all workers:

    bin/console app:worker:start-all

To stop single worker:

    bin/console app:worker:stop WorkerName

To stop all workers:

    bin/console app:worker:stop-all

All available commands:

```
Available commands for the "app" namespace:
  app:worker:restart      
  app:worker:restart-all  
  app:worker:start        
  app:worker:start-all    
  app:worker:stop         
  app:worker:stop-all  
```

## Maintaining workers using `cron`

To make sure that workers are running, on a Shared Hosting server you can add the command `app:worker:start-all` 
to `cron` to run every minute. The command only creates workers if the processes are not running.

If a process terminates unexpectedly, cron will resume it.

This is a solution in case there is no access to Supervisor, Systemd, etc.

Cron example:

    * * * * * cd /path/to/your/application && bin/console app:worker:start-all

You can also restart workers at a specific time to protect yourself from hang state processes:

    0 3 * * * cd /path/to/your/application && bin/console app:worker:restart-all

## Testing

Checking if the software is working properly on your server:

    composer test

## Copyrights

Copyright © Rafał Mikołajun 2024.

