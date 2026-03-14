<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Employee,Attendance,Leave,SiteExpense,Correspondence,Task,Payroll};
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::whereNotIn("employee_code",["SYS-001"])->get();

        // Attendance last 30 days
        foreach (range(30, 1) as $daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
            if ($date->isWeekend()) continue;
            foreach ($employees->random(min(12, $employees->count())) as $emp) {
                $late = rand(0,5) === 0;
                Attendance::firstOrCreate(
                    ["employee_id"=>$emp->id,"date"=>$date->toDateString()],
                    ["clock_in"=>$late?"08:".rand(35,59):"07:".rand(30,59),
                     "clock_out"=>"17:".rand(0,30),"is_late"=>$late,"status"=>"present"]
                );
            }
        }
        echo "✅ Attendance seeded\n";

        // Payrolls
        foreach ($employees->take(8) as $emp) {
            $gross = rand(800000, 3500000);
            Payroll::firstOrCreate(
                ["employee_id"=>$emp->id,"period"=>now()->format("Y-m")],
                ["gross_pay"=>$gross,"net_pay"=>round($gross*0.78),
                 "pay_date"=>now()->startOfMonth()->addDays(25),
                 "status"=>"completed","paye_amount"=>round($gross*0.12),
                 "nssf_employee_amount"=>round($gross*0.05),
                 "nssf_employer_amount"=>round($gross*0.10),
                 "lst_amount"=>25000]
            );
        }
        echo "✅ Payrolls seeded\n";

        echo "Demo data complete!\n";
    }
}
