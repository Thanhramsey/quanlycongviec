<?php

namespace App\Commands;

use App\Models\DailyProgressLogModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AutoApproveProgressLogs extends BaseCommand
{
    protected $group = 'maintenance';
    protected $name = 'logs:auto-approve';
    protected $description = 'Tu dong duyet bao cao pending da qua nguong 48 gio.';

    public function run(array $params)
    {
        $model = new DailyProgressLogModel();
        $affected = $model->autoApproveOldLogs();

        if ($affected > 0) {
            CLI::write("Da tu dong duyet {$affected} bao cao pending.", 'green');
            return;
        }

        CLI::write('Khong co bao cao nao du dieu kien tu dong duyet.', 'yellow');
    }
}
