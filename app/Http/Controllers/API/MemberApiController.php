<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;

class MemberApiController extends Controller
{
    /**
     * Search for members by name or contact
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $members = Membership::where('name', 'LIKE', "%{$query}%")
            ->orWhere('contact', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'contact', 'plan_type', 'status', 'due_date', 'avatar', 'is_student', 'student_id']);

        return response()->json($members);
    }

    /**
     * Get member details by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $member = Membership::findOrFail($id);

            return response()->json([
                'success' => true,
                'member' => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'contact' => $member->contact,
                    'avatar' => $member->avatar,
                    'plan_type' => $member->plan_type,
                    'status' => $member->status,
                    'start_date' => $member->start_date,
                    'due_date' => $member->due_date,
                    'is_active' => $member->status === 'Active' && $member->due_date && $member->due_date->isFuture(),
                    'is_student' => (bool) $member->is_student,
                    'student_id' => $member->student_id,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }
    }
}