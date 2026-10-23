<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
class ProcessPosSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
    protected function prepareForValidation(): void
    {
        $customerId = $this->input('customer_id');
        if ($customerId === '' || $customerId === '0' || $customerId === 0) {
            $this->merge(['customer_id' => null]);
        }
    }
    public function rules(): array
    {
        return [
            'items'               => ['required', 'array', 'min:1'],
            'items.*.id'          => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'    => ['required', 'integer', 'min:1'],
            'items.*.discount'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.variation_id'=> ['nullable', 'integer', 'exists:product_variations,id'],
            'items.*.code'        => ['nullable', 'string', 'max:100'],
            'payment_method'      => ['required', 'string', 'max:50'],
            'paid_amount'         => ['required', 'numeric', 'min:0.01'],
            'customer_id'         => ['required', 'integer', 'exists:customers,id'],
            'customer_name'       => ['nullable', 'string', 'max:255'],
            'customer_phone'      => ['nullable', 'string', 'max:50'],
            'customer_address'    => ['nullable', 'string', 'max:500'],
            'vat_rate'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_type'       => ['nullable', 'string', 'in:percent,flat'],
            'discount_value'      => ['nullable', 'numeric', 'min:0'],
            'shipping'            => ['nullable', 'numeric', 'min:0'],
            'shipping_charge'     => ['nullable', 'numeric', 'min:0'],
            'notes'               => ['nullable', 'string', 'max:2000'],
        ];
    }
    public function messages(): array
    {
        return [
            'paid_amount.required' => 'Receive amount is required',
            'paid_amount.min'      => 'Receive amount must be greater than 0',
            'customer_id.required' => 'Please select a customer. Customer selection is mandatory.',
            'customer_id.exists'   => 'Selected customer not found. Please select a valid customer.',
        ];
    }
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422));
    }
}
