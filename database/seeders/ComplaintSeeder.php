<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Complaint;
use App\Models\User;

class ComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run()
    {
        $customers = User::where('role', 'customer')->get();
        $technicians = User::where('role', 'technician')->get();

        $complaints = [
            [
                'title' => 'Internet Connection Issue',
                'description' => 'My internet connection keeps dropping every few minutes.',
                'status' => 'open',
            ],
            [
                'title' => 'Billing Discrepancy',
                'description' => 'I was charged twice for the same service this month.',
                'status' => 'in_progress',
            ],
            [
                'title' => 'Service Outage',
                'description' => 'Complete service outage in my area since yesterday.',
                'status' => 'resolved',
            ],
        ];

        foreach ($complaints as $index => $complaintData) {
            $complaint = Complaint::create([
                'title' => $complaintData['title'],
                'description' => $complaintData['description'],
                'status' => $complaintData['status'],
                'customer_id' => $customers->random()->id,
            ]);

            // Assign technician for non-open complaints
            if ($complaintData['status'] !== 'open') {
                $complaint->update([
                    'technician_id' => $technicians->random()->id,
                    'assigned_at' => now()->subHours(rand(1, 24)),
                ]);
            }

            // Set resolved_at for resolved complaints
            if ($complaintData['status'] === 'resolved') {
                $complaint->update([
                    'resolved_at' => now()->subHours(rand(1, 12)),
                ]);
            }
        }
    }
}
