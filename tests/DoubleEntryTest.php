<?php

// ensure we load our base file (PHPStorm Bug when using remote interpreter )
require_once('BaseTest.php');

use Scottlaurent\Accounting\Services\Accounting as AccountingService;
use \Scottlaurent\Accounting\Exceptions\{InvalidJournalMethod, InvalidJournalEntryValue, DebitsAndCreditsDoNotEqual};
use Scottlaurent\Accounting\TestJournalTransaction as JournalTransaction;

class DoubleEntryTest extends BaseTest
{
    public function testMakingSureWeOnlySendDebitOrCreditCommands():void
    {
        $this->expectException(InvalidJournalMethod::class);
        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'banana', 100);
    }

    public function testMakingSureDoubleEntryValueIsNotZero():void
    {
        $this->expectException(InvalidJournalEntryValue::class);
        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', 0);
    }

    public function testMakingSureDoubleEntryValueIsNotNegative():void
    {
        $this->expectException(InvalidJournalEntryValue::class);
        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', 0);
    }

    public function testMakingSureDoubleEntryCreditsAndDebitsMatch(): void
    {
        $this->expectException(DebitsAndCreditsDoNotEqual::class);
        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', 99.01);
        $transaction_group->addDollarTransaction($this->company_ar_journal, 'credit', 99.00);
        $transaction_group->commit();
    }

    public function testMakingSurePostTransactionJournalValuesMatch(): void
    {
        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', 100);
        $transaction_group->addDollarTransaction($this->company_ar_journal, 'credit', 100);
        $transaction_group->commit();
        $this->assertEquals($this->company_cash_journal->getCurrentBalanceInDollars(),
            (-1) * $this->company_ar_journal->getCurrentBalanceInDollars());
    }

    public function testTransactionGroupsMatch(): void
    {
        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', 100);
        $transaction_group->addDollarTransaction($this->company_ar_journal, 'credit', 100);
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', 75);
        $transaction_group->addDollarTransaction($this->company_ar_journal, 'credit', 75);
        $transaction_group_uuid = $transaction_group->commit();

        $this->assertEquals(JournalTransaction::where('transaction_group', $transaction_group_uuid)->count(), 4);
    }

    public function testMakingSurePostTransactionLedgersMatch()
    {
        $dollar_value = mt_rand(1000000, 9999999) * 1.987654321;

        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', $dollar_value);
        $transaction_group->addDollarTransaction($this->company_income_journal, 'credit', $dollar_value);
        $transaction_group->commit();

        $this->assertEquals($this->company_assets_ledger->getCurrentBalanceInDollars($this->currency),
            ((int)($dollar_value * 100)) / 100);
        $this->assertEquals($this->company_income_ledger->getCurrentBalanceInDollars($this->currency),
            ((int)($dollar_value * 100)) / 100);

        $this->assertEquals(
            $this->company_assets_ledger->getCurrentBalanceInDollars($this->currency),
            $this->company_income_ledger->getCurrentBalanceInDollars($this->currency)
        );
    }

    public function testMakingSurePostTransactionLedgersMatchAfterComplexActivity(): void
    {
        for ($x = 1; $x <= 1000; $x++) {

            $dollar_value_a = mt_rand(1, 99999999) * 2.25;
            $dollar_value_b = mt_rand(1, 99999999) * 3.50;

            $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
            $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', $dollar_value_a);
            $transaction_group->addDollarTransaction($this->company_ar_journal, 'debit', $dollar_value_b);
            $transaction_group->addDollarTransaction($this->company_income_journal, 'credit',
                $dollar_value_a + $dollar_value_b);
            $transaction_group->commit();
        }

        $this->assertEquals(
            $this->company_assets_ledger->getCurrentBalanceInDollars('$this->currency'),
            $this->company_income_ledger->getCurrentBalanceInDollars('$this->currency')
        );
    }
}
