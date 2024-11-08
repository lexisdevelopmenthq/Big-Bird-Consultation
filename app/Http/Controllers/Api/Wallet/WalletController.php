<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\Controller;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class WalletController extends Controller
{
use HttpResponses;

// Get the balance of the authenticated user's wallet
public function balance()
{
    try {
        // Get the authenticated user
        $user = Auth::user();

     

        $balance = $user->balance;
        return $this->success(['balance' => $balance], 'Wallet balance retrieved successfully');
    } catch (Throwable $e) {
        return $this->error(null, 'Failed to retrieve wallet balance: ' . $e->getMessage(), 500);
    }
}


// Deposit funds to the authenticated user's wallet
public function deposit(Request $request)
{
    $request->validate([
        'amount' => 'required|numeric|min:1'
    ]);

    try {
        Auth::user()->deposit($request->amount);
        return $this->success(null, 'Amount deposited successfully');
    } catch (Throwable $e) {
        return $this->error(null, 'Failed to deposit amount', 500);
    }
}

// Withdraw funds from the authenticated user's wallet
public function withdraw(Request $request)
{
    $request->validate([
        'amount' => 'required|numeric|min:1'
    ]);

    try {
        $user = Auth::user();
        if ($user->balance < $request->amount) {
            return $this->error(null, 'Insufficient balance', 400);
        }

        $user->withdraw($request->amount);
        return $this->success(null, 'Amount withdrawn successfully');
    } catch (Throwable $e) {
        return $this->error(null, 'Failed to withdraw amount', 500);
    }
}
}
