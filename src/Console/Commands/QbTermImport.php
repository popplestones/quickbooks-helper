<?php

namespace Popplestones\Quickbooks\Console\Commands;

use Illuminate\Console\Command;
use Popplestones\Quickbooks\Services\QuickbooksHelper;

class QbTermImport extends Command
{
    use SyncsWithQuickbooks;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qb:term:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import terms from Quickbooks';

    public $modelName;
    public $mapping;
    public $qb_helper;

    private function setup()
    {
        $this->modelName = config('quickbooks.term.model');
        $this->mapping = config('quickbooks.term.attributeMap');
        $this->qb_helper = new QuickbooksHelper();
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setup();
        if (!$this->checkConnection()) return 1;

        $this->info("Importing terms to {$this->modelName}");
        $this->importModels(
            modelName: $this->modelName,
            tableName: 'Term',
            callback: fn($row) =>
            app($this->modelName)::updateOrCreate([$this->mapping['qb_term_id'] => $row->Id], $this->setDataMapping($row, $this->mapping))
        );

        return 0;
    }


    protected function setDataMapping($row, $mapping)
    {
        return [
            $mapping['name'] => $row->Name,
            $mapping['discount_percent'] => $row->DiscountPercent,
            $mapping['discount_days'] => $row->DiscountDays,
            $mapping['active'] => $row->Active === 'true',
            $mapping['type'] => $row->Type,
            $mapping['day_of_month_due'] => $row->DayOfMonthDue,
            $mapping['discount_day_of_month'] => $row->DiscountDayOfMonth,
            $mapping['due_next_month_days'] => $row->DueNextMonthDays,
            $mapping['due_days'] => $row->DueDays
        ];
    }
}
