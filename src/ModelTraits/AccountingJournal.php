<?php

declare(strict_types=1);

namespace Scottlaurent\Accounting\ModelTraits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Scottlaurent\Accounting\Exceptions\JournalAlreadyExists;
use Scottlaurent\Accounting\Models\Journal;

trait AccountingJournal
{

    protected function getJournalClass()
    {
        return isset($this->journalClass) ? $this->journalClass : Journal::class;
    }

    public function journal(): MorphOne
    {
        return $this->morphOne($this->getJournalClass(), 'morphed');
    }

    /**
     * Initialize a journal for a given model object
     *
     * @param null|string $currency_code
     * @param null|string $ledger_id
     * @return mixed
     * @throws JournalAlreadyExists
     */
    public function initJournal(?string $currency_code = null, ?string $ledger_id = null)
    {
        if (!$this->journal) {
            $journalClassName = $this->getJournalClass();
            $journal = new $journalClassName();
            if ($ledger_id) {
                $journal->ledger_id = $ledger_id;
            }
            $journal->currency = $currency_code ?? config('accounting.base_currency');
            $journal->balance = 0;
            return $this->journal()->save($journal);
        }
        throw new JournalAlreadyExists;
    }
}
