<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Coroutine\Parallel;
use Hyperf\Context\Context;
use function Hyperf\Coroutine\go;
use function Hyperf\Support\now;

class BenchmarkController
{
    // 4. Cenário INSANO (500 tasks)
    // Simula 500 requisições simultâneas.
    // Sync: Levaria ~2.5 minutos.
    // Async: Deve levar ~1 segundo.
    public function insaneMode()
    {
        $start = microtime(true);
        $totalCount = 500;
        $batchSize = 100;
        $batches = ceil($totalCount / $batchSize);
        
        for ($b = 0; $b < $batches; $b++) {
            $parallel = new Parallel($batchSize);
            for ($i = 0; $i < $batchSize; $i++) {
                $parallel->add(function () {
                    // Tempo aleatório entre 0.1s e 0.5s
                    $delay = rand(100, 500) / 1000; 
                    usleep((int)($delay * 1000000));
                    return true;
                });
            }
            $parallel->wait();
        }

        $end = microtime(true);
        $duration = round($end - $start, 4);

        return [
            'mode' => 'INSANE MODE (500 tasks)',
            'tasks_count' => $totalCount,
            'duration_seconds' => $duration,
            'message' => "INSANO! Processamos $totalCount tarefas em apenas $duration segundos! No modo tradicional levaria ~2.5 minutos.",
        ];
    }

    // 3. Cenário Heavy Load (Ousado)
    // Simula 50 requisições simultâneas variando entre 0.1s e 0.5s
    // Sync: Levaria ~15 segundos.
    // Async: Deve levar ~0.5 segundos.
    public function heavy()
    {
        $start = microtime(true);
        $count = 50;
        $parallel = new Parallel($count);

        for ($i = 1; $i <= $count; $i++) {
            $parallel->add(function () use ($i) {
                // Tempo aleatório entre 0.1s e 0.5s
                $delay = rand(100, 500) / 1000; 
                usleep((int)($delay * 1000000));
                return "Task $i done in {$delay}s";
            });
        }

        $results = $parallel->wait();

        $end = microtime(true);
        $duration = round($end - $start, 4);

        return [
            'mode' => 'HEAVY LOAD (50 tasks)',
            'tasks_count' => $count,
            'duration_seconds' => $duration,
            'message' => "Processamos $count tarefas pesadas em apenas $duration segundos! No modo tradicional levaria mais de 15s.",
        ];
    }

    // 5. Cenário GOD MODE (1000 tasks)
    // Simula 1000 requisições simultâneas.
    // Sync: Levaria ~5 minutos.
    // Async: Deve levar ~1 segundo.
    public function god()
    {
        $start = microtime(true);
        $count = 1000;
        
        $parallel = new Parallel($count);

        for ($i = 1; $i <= $count; $i++) {
            $parallel->add(function () use ($i) {
                // Tempo aleatório entre 0.1s e 0.5s
                $delay = rand(100, 500) / 1000; 
                usleep((int)($delay * 1000000));
                return "Task $i done in {$delay}s";
            });
        }

        $results = $parallel->wait();

        $end = microtime(true);
        $duration = round($end - $start, 4);

        return [
            'mode' => 'GOD MODE (1000 tasks)',
            'tasks_count' => $count,
            'duration_seconds' => $duration,
            'message' => "GOD MODE! Processamos $count tarefas em apenas $duration segundos! No modo tradicional levaria ~5 minutos.",
        ];
    }

    // 6. Cenário SINGULARITY (10.000 tasks)
    // Simula 10.000 requisições.
    // Estratégia: Batching (Lotes) de 500 para não estourar memória/sockets.
    // Sync: Levaria ~50 minutos.
    // Async: Deve levar ~5-6 segundos.
    public function singularity()
    {
        $start = microtime(true);
        $totalCount = 10000;
        $batchSize = 500;
        $batches = ceil($totalCount / $batchSize);
        
        for ($b = 0; $b < $batches; $b++) {
            $parallel = new Parallel($batchSize);
            for ($i = 0; $i < $batchSize; $i++) {
                $parallel->add(function () {
                    // Tempo aleatório entre 0.1s e 0.5s
                    $delay = rand(100, 500) / 1000; 
                    usleep((int)($delay * 1000000));
                    return true;
                });
            }
            $parallel->wait();
        }

        $end = microtime(true);
        $duration = round($end - $start, 4);

        return [
            'mode' => 'SINGULARITY MODE (10.000 tasks)',
            'tasks_count' => $totalCount,
            'batch_size' => $batchSize,
            'duration_seconds' => $duration,
            'message' => "SINGULARITY! Processamos $totalCount tarefas em apenas $duration segundos! No modo tradicional levaria quase 1 hora.",
        ];
    }

    private function simulateSlowTask($name, $seconds)
    {
        sleep($seconds);
        return "$name completed in {$seconds}s";
    }
}
