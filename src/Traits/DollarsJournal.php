<?php

namespace Scottlaurent\Accounting\Traits;

use Carbon\Carbon;
use Scottlaurent\Accounting\Models\JournalTransaction;

trait DollarsJournal
{
    /**
     * Get the balance of the journal in dollars.  This "could" include future dates.
     * @return float|int
     */
    public function getCurrentBalanceInDollars()
    {
        return $this->getCurrentBalance()->getAmount() / 100;
    }

    /**
     * Get balance
     * @return float|int
     */
    public function getBalanceInDollars()
    {
        return $this->getBalance()->getAmount() / 100;
    }

    /**
     * Credit a journal by a given dollar amount
     * @param Money|float $value
     * @param string  $memo
     * @param Carbon $post_date
     * @return JournalTransaction
     */
    public function creditDollars($value, string $memo = null, Carbon $post_date = null): JournalTransaction
    {
        $value = (int)($value * 100);
        return $this->credit($value, $memo, $post_date);
    }

    /**
     * Debit a journal by a given dollar amount
     * @param Money|float $value
     * @param string $memo
     * @param Carbon $post_date
     * @return JournalTransaction
     */
    public function debitDollars($value, string $memo = null, Carbon $post_date = null): JournalTransaction
    {
        $value = (int)($value * 100);
        return $this->debit($value, $memo, $post_date);
    }

    /**
     * Calculate the dollar amount debited to a journal today
     * @return float|int
     */
    public function getDollarsDebitedToday()
    {
        $today = Carbon::now();
        return $this->getDollarsDebitedOn($today);
    }

    /**
     * Calculate the dollar amount credited to a journal today
     * @return float|int
     */
    public function getDollarsCreditedToday()
    {
        $today = Carbon::now();
        return $this->getDollarsCreditedOn($today);
    }


    /**
     * Calculate the dollar amount debited to a journal on a given day
     * @param Carbon $date
     * @return float|int
     */
    public function getDollarsDebitedOn(Carbon $date)
    {
        return $this
            ->transactions()
            ->whereBetween('post_date', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay()
            ])
            ->sum('debit') / 100;
    }

    /**
     * Calculate the dollar amount credited to a journal on a given day
     * @param Carbon $date
     * @return float|int
     */
    public function getDollarsCreditedOn(Carbon $date)
    {
        return $this
            ->transactions()
            ->whereBetween('post_date', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay()
            ])
            ->sum('credit') / 100;
    }
}
