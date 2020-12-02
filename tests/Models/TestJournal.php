<?php

namespace Models;

use Scottlaurent\Accounting\Models\Journal;
use Scottlaurent\Accounting\Traits\DollarsJournal;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Scottlaurent\Accounting\TestJournalTransaction;

class TestJournal extends Journal
{
    use DollarsJournal;


    public function transactions(): HasMany
    {
        return $this->hasMany(TestJournalTransaction::class, 'journal_id', 'id');
    }

}
