<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRefundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For now, any authenticated user can process refunds
        // Can be enhanced with policy checks later
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:pos,membership',
            'payment_id' => 'required|integer|min:1',
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_reason' => 'required|string|min:3|max:500',
            'refund_method' => 'required|in:cash,card_reversal,store_credit',
            'product_name' => 'nullable|string|max:255',
            'refund_quantity' => 'nullable|integer|min:1',
            'processed_by' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validation errors
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Payment type is required.',
            'type.in' => 'Invalid payment type.',
            'payment_id.required' => 'Payment ID is required.',
            'payment_id.integer' => 'Payment ID must be an integer.',
            'refund_amount.required' => 'Refund amount is required.',
            'refund_amount.numeric' => 'Refund amount must be a valid number.',
            'refund_amount.min' => 'Refund amount must be greater than 0.',
            'refund_reason.required' => 'Refund reason is required.',
            'refund_reason.min' => 'Refund reason must be at least 3 characters.',
            'refund_reason.max' => 'Refund reason cannot exceed 500 characters.',
            'refund_method.required' => 'Refund method is required.',
            'refund_method.in' => 'Invalid refund method. Choose: cash, card_reversal, or store_credit.',
        ];
    }
}
