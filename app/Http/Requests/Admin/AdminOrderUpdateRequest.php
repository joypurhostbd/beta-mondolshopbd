<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminOrderUpdateRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $courierStatusIds = OrderStatus::whereIn('slug', Order::getCourierFilterSlugs())->pluck('id')->toArray();
        $orderId = $this->input('order_id') ?: $this->order_id;
        $order = $orderId ? Order::find($orderId) : null;
        $currentStatus = $order ? (int) $order->order_status : null;
        $forbiddenStatuses = array_values(array_diff($courierStatusIds, array_filter([$currentStatus])));

        return [
            'order_id' => 'required|exists:orders,id',
            'name' => 'required|string|max:155',
            'phone' => 'required|string|max:55',
            'address' => 'required|string|max:1000',
            'area' => 'required',
            'order_status' => [
                'nullable',
                'integer',
                Rule::notIn($forbiddenStatuses),
            ],
            'payment_method' => 'nullable|string|max:100',
            'payment_status' => 'nullable|string|max:50',
            'trx_id' => 'nullable|string|max:100',
            'sender_number' => 'nullable|string|max:50',
            'advance_amount' => 'nullable|numeric|min:0',
            'custom_shipping' => 'nullable|numeric|min:0',
            'order_discount' => 'nullable|numeric|min:0',
            'admin_note' => 'nullable|string|max:2000',
            'note' => 'nullable|string|max:2000',
        ];
    }
}
