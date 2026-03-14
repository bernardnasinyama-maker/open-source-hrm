<?php
namespace App\Observers;
use App\Models\SiteExpense;
use Illuminate\Support\Facades\Auth;

class SiteExpenseObserver
{
    public function updating(SiteExpense $expense): void
    {
        $changes = [];
        foreach ($expense->getDirty() as $field => $newValue) {
            $oldValue = $expense->getOriginal($field);
            if ($oldValue != $newValue) {
                $changes[] = "{$field}: {$oldValue} → {$newValue}";
            }
        }
        if (!empty($changes)) {
            activity("expense_audit")
                ->performedOn($expense)
                ->causedBy(Auth::user())
                ->withProperties(["changes" => $changes, "editor" => Auth::user()?->email])
                ->log("Expense updated: " . implode(", ", $changes));
        }
    }
}