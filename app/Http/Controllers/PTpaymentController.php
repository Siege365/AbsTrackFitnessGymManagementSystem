<?php

namespace App\Http\Controllers;

use App\Models\GymPlan;
use Illuminate\Http\Request;

class PTpaymentController extends Controller
{
    public function index()
    {
        $ptPlans = GymPlan::active()->personalTraining()->ordered()->get();

        $trainers = [
            'David Laid',
            'Eulo Icon Sexcion',
            'Justin Troy Rosalada',
            'Nicolas Deloso Torre III',
            'Ronnie Coleman',
        ];

        return view('PaymentAndBillings.pt-payment', compact('ptPlans', 'trainers'));
    }
}
