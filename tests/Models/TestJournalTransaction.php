<?php

namespace Scottlaurent\Accounting;

use Models\TestJournal;
use Scottlaurent\Accounting\Models\JournalTransaction;

class TestJournalTransaction extends JournalTransaction 
{
    public function journal()
    {
        return $this->belongsTo(TestJournal::class, 'id', 'journal_id');
    }
}
