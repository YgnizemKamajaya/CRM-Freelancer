<?php

use App\Livewire\BoardShow;
use App\Models\Board;
use App\Models\Card;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\FinanceService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// Run explicitly against the dedicated disposable MySQL schema, never the app DB.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (! app()->environment('testing') || config('database.default') !== 'mysql'
    || config('database.connections.mysql.database') !== 'minicrm_audit_concurrency') {
    fwrite(STDERR, "Requires APP_ENV=testing and disposable MySQL DB minicrm_audit_concurrency.\n");
    exit(1);
}

if (($argv[1] ?? '') === 'worker') {
    [$script, $mode, $kind, $recordId, $clientId, $barrier, $workerId] = $argv;
    touch($barrier.'/ready-'.$workerId);
    $deadline = microtime(true) + 20;
    while (! file_exists($barrier.'/go')) {
        if (microtime(true) > $deadline) {
            throw new RuntimeException('Concurrency barrier timed out.');
        }
        usleep(10000);
    }
    if ($kind === 'payment') {
        app(FinanceService::class)->recordInvoicePayment(Invoice::findOrFail($recordId));
    } else {
        $card = Card::findOrFail($recordId);
        $component = new BoardShow;
        $component->mount($card->column->board);
        $component->promotionClientId = (int) $clientId;
        $component->convertToProject((int) $recordId);
    }
    exit(0);
}

Artisan::call('migrate:fresh', ['--force' => true]);
$client = Client::create(['name' => 'Concurrency fixture']);
Wallet::create(['name' => 'Fixture wallet', 'currency' => 'IDR']);
$invoice = Invoice::create(['client_id' => $client->id, 'invoice_number' => 'CONCURRENT', 'issue_date' => today(), 'currency' => 'IDR', 'total' => 100, 'amount_paid' => 100, 'status' => 'paid']);
$board = Board::create(['name' => 'Concurrent board']);
$card = $board->columns()->create(['name' => 'Ideas'])->cards()->create(['title' => 'Concurrent idea']);

foreach (['payment' => $invoice->id, 'promotion' => $card->id] as $kind => $recordId) {
    $barrier = sys_get_temp_dir().'/minicrm-barrier-'.bin2hex(random_bytes(6));
    mkdir($barrier, 0700);
    $processes = [];
    for ($worker = 0; $worker < 8; $worker++) {
        $pipes = [];
        $process = proc_open([PHP_BINARY, __FILE__, 'worker', $kind, (string) $recordId, (string) $client->id, $barrier, (string) $worker], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (! is_resource($process)) {
            throw new RuntimeException('Worker did not start.');
        }
        fclose($pipes[0]);
        $processes[] = [$process, $pipes];
    }
    $deadline = microtime(true) + 20;
    while (count(glob($barrier.'/ready-*')) < 8) {
        if (microtime(true) > $deadline) {
            throw new RuntimeException('Workers did not reach barrier.');
        }
        usleep(10000);
    }
    touch($barrier.'/go');
    foreach ($processes as [$process, $pipes]) {
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        if (proc_close($process) !== 0) {
            throw new RuntimeException($kind.' worker failed: '.$stdout.$stderr);
        }
    }
    foreach (glob($barrier.'/*') as $file) {
        unlink($file);
    }
    rmdir($barrier);
}

$payment = Transaction::sole();
$project = Project::sole();
if ($payment->amount !== '100.00' || $payment->invoice_payment_id !== $invoice->id || $card->fresh()->project_id !== $project->id) {
    throw new RuntimeException('Concurrency invariants failed.');
}
echo json_encode(['database' => DB::selectOne('select version() as version')->version, 'workers_per_case' => 8, 'payment_rows' => 1, 'payment_amount' => $payment->amount, 'project_rows' => 1, 'card_link_matches' => true], JSON_PRETTY_PRINT).PHP_EOL;
