<?php

namespace Database\Seeders;

use App\Models\GymPlan;
use Illuminate\Database\Seeder;

class GymPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            // ── Membership Plans ──
            [
                'category'         => 'membership',
                'plan_name'        => 'Student Rate',
                'plan_key'         => 'Student',
                'price'            => 500.00,
                'duration_days'    => 30,
                'duration_label'   => 'Monthly',
                'badge_text'       => 'Student Only',
                'badge_color'      => 'info',
                'requires_student' => true,
                'requires_buddy'   => false,
                'buddy_count'      => 1,
                'description'      => 'Special rate for students with valid ID',
                'sort_order'       => 1,
            ],
            [
                'category'         => 'membership',
                'plan_name'        => 'Gym Buddy (2 persons)',
                'plan_key'         => 'GymBuddy',
                'price'            => 900.00,
                'duration_days'    => 30,
                'duration_label'   => 'Monthly',
                'badge_text'       => '2 People',
                'badge_color'      => 'warning',
                'requires_student' => false,
                'requires_buddy'   => true,
                'buddy_count'      => 2,
                'description'      => '₱450 per person when you bring a buddy',
                'sort_order'       => 2,
            ],
            [
                'category'         => 'membership',
                'plan_name'        => 'Monthly',
                'plan_key'         => 'Regular',
                'price'            => 600.00,
                'duration_days'    => 30,
                'duration_label'   => 'Monthly',
                'badge_text'       => null,
                'badge_color'      => null,
                'requires_student' => false,
                'requires_buddy'   => false,
                'buddy_count'      => 1,
                'description'      => 'Standard monthly gym membership',
                'sort_order'       => 3,
            ],
            [
                'category'         => 'membership',
                'plan_name'        => 'Quarterly',
                'plan_key'         => 'ThreeMonths',
                'price'            => 1650.00,
                'duration_days'    => 90,
                'duration_label'   => '3 Months',
                'badge_text'       => 'Best Value',
                'badge_color'      => 'success',
                'requires_student' => false,
                'requires_buddy'   => false,
                'buddy_count'      => 1,
                'description'      => '3-month membership at a discounted rate',
                'sort_order'       => 4,
            ],
            [
                'category'         => 'membership',
                'plan_name'        => 'Half-Yearly',
                'plan_key'         => 'HalfYearly',
                'price'            => 3000.00,
                'duration_days'    => 180,
                'duration_label'   => '6 Months',
                'badge_text'       => null,
                'badge_color'      => null,
                'requires_student' => false,
                'requires_buddy'   => false,
                'buddy_count'      => 1,
                'description'      => '6-month membership plan',
                'sort_order'       => 5,
            ],
            [
                'category'         => 'membership',
                'plan_name'        => 'Annual',
                'plan_key'         => 'Annual',
                'price'            => 5500.00,
                'duration_days'    => 365,
                'duration_label'   => '12 Months',
                'badge_text'       => null,
                'badge_color'      => null,
                'requires_student' => false,
                'requires_buddy'   => false,
                'buddy_count'      => 1,
                'description'      => 'Full-year membership plan',
                'sort_order'       => 6,
            ],
            [
                'category'         => 'membership',
                'plan_name'        => 'Session Pass',
                'plan_key'         => 'Session',
                'price'            => 50.00,
                'duration_days'    => 1,
                'duration_label'   => 'Per Session',
                'badge_text'       => null,
                'badge_color'      => null,
                'requires_student' => false,
                'requires_buddy'   => false,
                'buddy_count'      => 1,
                'description'      => 'Single-day access pass',
                'sort_order'       => 7,
            ],

            // ── Personal Training Plans ──
            [
                'category'         => 'personal_training',
                'plan_name'        => 'Per Session',
                'plan_key'         => 'PTSession',
                'price'            => 250.00,
                'duration_days'    => 1,
                'duration_label'   => 'Per Session',
                'badge_text'       => null,
                'badge_color'      => null,
                'requires_student' => false,
                'requires_buddy'   => false,
                'buddy_count'      => 1,
                'description'      => 'Single personal training session',
                'sort_order'       => 1,
            ],
            [
                'category'         => 'personal_training',
                'plan_name'        => 'Monthly',
                'plan_key'         => 'PTMonthly',
                'price'            => 2000.00,
                'duration_days'    => 30,
                'duration_label'   => 'Monthly',
                'badge_text'       => null,
                'badge_color'      => null,
                'requires_student' => false,
                'requires_buddy'   => false,
                'buddy_count'      => 1,
                'description'      => 'Monthly personal training package',
                'sort_order'       => 2,
            ],
        ];

        foreach ($plans as $plan) {
            GymPlan::updateOrCreate(
                ['plan_key' => $plan['plan_key']],
                $plan
            );
        }
    }
}
