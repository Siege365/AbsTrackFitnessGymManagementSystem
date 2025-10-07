<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::latest()->paginate(10);
        
        // Calculate statistics
        $totalClients = Client::count();
        $activeClients = Client::where('status', 'Active')->count();
        $expiringThisWeek = Client::where('status', 'Due soon')->count();
        $newSignupsThisMonth = Client::whereMonth('created_at', now()->month)->count();
        
        return view('clients.index', compact(
            'clients',
            'totalClients',
            'activeClients',
            'expiringThisWeek',
            'newSignupsThisMonth'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'age' => 'nullable|integer|min:1|max:120',
                'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
                'plan_type' => 'required|in:Monthly,Session',
                'start_date' => 'required|date',
                'due_date' => 'required|date|after:start_date',
                'contact' => ['required', 'string', 'max:255', 'regex:/^[+]?[0-9() ]+$/'],
            ]);

            // Calculate status automatically based on dates
            $validated['status'] = $this->calculateStatus($validated['start_date'], $validated['due_date']);

            if ($request->hasFile('avatar')) {
                try {
                    $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Failed to upload avatar. Please try again.');
                }
            }

            Client::create($validated);

            return redirect()->route('clients.index')
                ->with('success', 'Client created successfully!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the client. Please try again.');
        }
    }

    /**
     * Calculate client status based on dates.
     */
    private function calculateStatus($startDate, $dueDate)
    {
        try {
            $today = now()->startOfDay();
            $dueDate = \Carbon\Carbon::parse($dueDate)->startOfDay();
            $daysUntilDue = $today->diffInDays($dueDate, false);

            // If due date has passed (negative days), status is Expired
            if ($daysUntilDue < 0) {
                return 'Expired';
            }
            
            // If due date is within 7 days, status is Due soon
            if ($daysUntilDue <= 7) {
                return 'Due soon';
            }
            
            // Otherwise, status is Active
            return 'Active';
        } catch (\Exception $e) {
            // Default to Active if there's any error in calculation
            Log::warning("Error calculating status: " . $e->getMessage());
            return 'Active';
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $client = Client::findOrFail($id);
            return view('clients.show', compact('client'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('clients.index')
                ->with('error', 'Client not found.');
        } catch (\Exception $e) {
            return redirect()->route('clients.index')
                ->with('error', 'An error occurred while retrieving the client.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $client = Client::findOrFail($id);
            return view('clients.edit', compact('client'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('clients.index')
                ->with('error', 'Client not found.');
        } catch (\Exception $e) {
            return redirect()->route('clients.index')
                ->with('error', 'An error occurred while loading the edit form.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $client = Client::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'age' => 'nullable|integer|min:1|max:120',
                'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
                'plan_type' => 'required|in:Monthly,Session',
                'start_date' => 'required|date',
                'due_date' => 'required|date|after:start_date',
                'contact' => ['required', 'string', 'max:255', 'regex:/^[+]?[0-9() ]+$/'],
            ]);

            // Recalculate status automatically based on updated dates
            $validated['status'] = $this->calculateStatus($validated['start_date'], $validated['due_date']);

            if ($request->hasFile('avatar')) {
                try {
                    // Delete old avatar
                    if ($client->avatar && Storage::disk('public')->exists($client->avatar)) {
                        Storage::disk('public')->delete($client->avatar);
                    }
                    $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Failed to upload new avatar. Please try again.');
                }
            }

            $client->update($validated);

            return redirect()->route('clients.index')
                ->with('success', 'Client updated successfully!');
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('clients.index')
                ->with('error', 'Client not found.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the client. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $client = Client::findOrFail($id);
            
            // Delete avatar if exists
            if ($client->avatar) {
                try {
                    if (Storage::disk('public')->exists($client->avatar)) {
                        Storage::disk('public')->delete($client->avatar);
                    }
                } catch (\Exception $e) {
                    // Log error but continue with deletion
                    Log::warning("Failed to delete avatar for client {$id}: " . $e->getMessage());
                }
            }
            
            $client->delete();

            return redirect()->route('clients.index')
                ->with('success', 'Client deleted successfully!');
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('clients.index')
                ->with('error', 'Client not found.');
        } catch (\Exception $e) {
            return redirect()->route('clients.index')
                ->with('error', 'An error occurred while deleting the client. Please try again.');
        }
    }

    /**
     * Delete multiple clients at once
     */
    public function bulkDelete(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'client_ids' => 'required|array',
                'client_ids.*' => 'exists:clients,id'
            ]);

            $clientIds = $request->client_ids;
            $deletedCount = 0;
            $errors = [];

            foreach ($clientIds as $id) {
                try {
                    $client = Client::findOrFail($id);
                    
                    // Delete avatar if exists
                    if ($client->avatar && Storage::exists('public/' . $client->avatar)) {
                        Storage::delete('public/' . $client->avatar);
                    }
                    
                    // Delete the client
                    $client->delete();
                    $deletedCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Failed to delete client ID: {$id}";
                    Log::error("Bulk delete error for client {$id}: " . $e->getMessage());
                }
            }

            // Prepare response message
            if ($deletedCount > 0) {
                $message = "Successfully deleted {$deletedCount} client" . ($deletedCount > 1 ? 's' : '') . ".";
                
                if (count($errors) > 0) {
                    $message .= " However, " . count($errors) . " client" . (count($errors) > 1 ? 's' : '') . " could not be deleted.";
                }
                
                return redirect()->route('clients.index')
                    ->with('success', $message);
            } else {
                return redirect()->route('clients.index')
                    ->with('error', 'No clients were deleted. ' . implode(', ', $errors));
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('clients.index')
                ->with('error', 'Invalid request. Please select valid clients to delete.');
        } catch (\Exception $e) {
            Log::error('Bulk delete error: ' . $e->getMessage());
            return redirect()->route('clients.index')
                ->with('error', 'An error occurred while deleting clients. Please try again.');
        }
    }
}
