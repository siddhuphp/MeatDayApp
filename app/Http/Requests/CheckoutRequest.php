<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'order_type' => 'required|in:immediate,pre_order',
            'delivery_date' => 'nullable|date|after:tomorrow',
            'payment_method' => 'required|in:payu,cod',
            'delivery_address' => 'required|string|max:500',
            'delivery_phone' => 'required|string|max:15',
            'delivery_name' => 'required|string|max:100',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate pre-order date for pre-orders
            if ($this->order_type === 'pre_order') {
                if (!$this->delivery_date) {
                    $validator->errors()->add('delivery_date', 'Delivery date is required for pre-orders.');
                } else {
                    $minDate = Carbon::tomorrow()->addDay(); // 2 days from now
                    if (Carbon::parse($this->delivery_date)->lt($minDate)) {
                        $validator->errors()->add('delivery_date', 'Delivery date must be at least 2 days from today.');
                    }
                }
            }

            // Validate COD availability for immediate orders
            if ($this->payment_method === 'cod' && $this->order_type === 'pre_order') {
                $validator->errors()->add('payment_method', 'Cash on delivery is not available for pre-orders.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'order_type.required' => 'Please select an order type.',
            'order_type.in' => 'Order type must be either immediate or pre-order.',
            'delivery_date.required' => 'Delivery date is required for pre-orders.',
            'delivery_date.date' => 'Please enter a valid delivery date.',
            'delivery_date.after' => 'Delivery date must be after tomorrow.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Payment method must be either payu or cod.',
            'delivery_address.required' => 'Delivery address is required.',
            'delivery_phone.required' => 'Delivery phone number is required.',
            'delivery_name.required' => 'Delivery name is required.',
        ];
    }
}
