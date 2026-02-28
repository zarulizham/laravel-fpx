<?php

namespace ZarulIzham\Fpx\Commands;

use Exception;
use Illuminate\Console\Command;
use ZarulIzham\Fpx\Messages\BankEnquiry;
use ZarulIzham\Fpx\Models\Bank;

class UpdateBankListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fpx:banks {--flow=01} {--debug=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update FPX banks List.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $handler = new BankEnquiry($this->option('flow'));

        $type = $this->option('flow') == '01' ? 'B2C' : 'B2B';

        $dataList = $handler->getData();

        try {
            $response = $handler->connect($dataList);

            $token = strtok($response, '&');
            $bankList = $handler->parseBanksList($token);

            if ($bankList === false) {
                $this->error('We could not find any data.');

                return 0;
            }

            $bar = $this->output->createProgressBar(count($bankList));
            $bar->start();

            foreach ($bankList as $key => $status) {
                $bankId = explode(' - ', $key)[1];
                $bank = $handler->getBanks($bankId, $type);
                if (empty($bank)) {
                    continue;
                }
                Bank::updateOrCreate([
                    'bank_id' => $bankId,
                    'type' => $bank['type'],
                ], [
                    'status' => $status == 'A' ? 'Online' : 'Offline',
                    'name' => $bank['name'],
                    'short_name' => $bank['short_name'],
                    'position' => $bank['position'] ?? 0,
                ]);

                $bar->advance();
            }

            $bar->finish();
            $this->info("\nBank list has been updated for ".$type);
            $this->newLine();

            return 1;
        } catch (Exception $e) {
            $this->error('request failed due to '.$e->getMessage());
            throw $e;
        }
    }
}
