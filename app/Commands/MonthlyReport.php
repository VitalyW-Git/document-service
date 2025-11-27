<?php

namespace App\Commands;

use App\Models\Document\FileModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MonthlyReport extends BaseCommand
{
    protected $group = 'Document';
    protected $name = 'document:monthly-report';
    protected $description = 'Выборка данных из БД Postgres с первого числа месяца по последнее число месяца';

    public function run(array $params)
    {
        $workerId = $params[0] ?? null;
        
        $currentDate = date('Y-m-d');
        $yearMonth = $this->convertDateToYYYYMM($currentDate);
        
        CLI::write("Текущая дата: {$currentDate}", 'yellow');
        CLI::write("Формат YYYYMM: {$yearMonth}", 'yellow');
        
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');
        
        CLI::write("Период выборки: с {$firstDayOfMonth} по {$lastDayOfMonth}", 'green');
        
        $fileModel = (new FileModel())
            ->where('created_at >=', $firstDayOfMonth . ' 00:00:00')
            ->where('created_at <=', $lastDayOfMonth . ' 23:59:59');
        
        if ($workerId !== null) {
            CLI::write("Фильтр по worker_id: {$workerId}", 'cyan');
        }
        
        $results = $fileModel->findAll();
        
        CLI::write("Найдено записей: " . count($results), 'green');
        
        if (count($results) > 0) {
            $headers = ['ID', 'Имя', 'Дата создания', 'Количество строк'];
            $rows = [];
            
            foreach ($results as $file) {
                $rows[] = [
                    substr($file->getId(), 0, 8) . '...',
                    $file->getName(),
                    $file->getCreatedAt(),
                    (string)$file->getRowCount()
                ];
            }
            
            CLI::table($rows, $headers);
        } else {
            CLI::write("Записи не найдены", 'red');
        }
        
        return EXIT_SUCCESS;
    }
    
    private function convertDateToYYYYMM(string $date): string
    {
        $timestamp = strtotime($date);
        return date('Ym', $timestamp);
    }
}

