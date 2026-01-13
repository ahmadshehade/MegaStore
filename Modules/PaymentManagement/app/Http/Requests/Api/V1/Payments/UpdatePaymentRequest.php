<?php

namespace Modules\PaymentManagement\Http\Requests\Api\V1\Payments;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Gate;
use Modules\PaymentManagement\Models\Payment;
use Modules\PaymentManagement\Models\Invoice;
use Illuminate\Contracts\Validation\Validator;

class UpdatePaymentRequest extends BaseRequest
{
    public function authorize(): bool
    {

        return Gate::allows('update', $this->route('payment'));
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => [
                'nullable',
                'integer',
                'exists:payment_methods,id',
            ],
            'amount' => [
                'nullable',
                'numeric',
                'gt:0',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'currency' => [
                'sometimes',
                'string',
                'size:3',
            ],
            'payment_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'payment_date' => [
                'nullable',
                'date',
                'before_or_equal:now',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method_id.integer' => 'Payment method ID must be a valid integer.',
            'payment_method_id.exists'  => 'The selected payment method does not exist.',
            'amount.numeric'  => 'Payment amount must be numeric.',
            'amount.gt'       => 'Payment amount must be greater than zero.',
            'amount.regex'    => 'Payment amount can have at most 2 decimal places.',
            'currency.string' => 'Currency must be a string value.',
            'currency.size'   => 'Currency must be a valid ISO-4217 code (e.g. USD, EUR).',
            'payment_notes.string' => 'Payment notes must be a valid text.',
            'payment_notes.max'    => 'Payment notes may not exceed 1000 characters.',
            'payment_date.date'           => 'Payment date must be a valid date.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function ($v) {
            $payment = $this->route('payment');
            if (!$payment) {
                $v->errors()->add('payment', 'Payment not found.');
                return;
            }

            $invoice = $payment->invoice()->with('payments')->first();
            if (!$invoice) {
                $v->errors()->add('invoice', 'Associated invoice not found.');
                return;
            }

            // إذا تم تعديل المبلغ
            if ($this->filled('amount')) {
                $newAmount = $this->input('amount');
                $paidAmount = $invoice->payments()
                    ->whereNull('deleted_at')
                    ->sum('amount') - $payment->amount;
                $remainingAfterUpdate = bcsub((string) $invoice->tot_amount, (string) $paidAmount, 2);
                $remainingAfterUpdate = bcsub((string) $remainingAfterUpdate, (string) $newAmount, 2);

                if (bccomp($remainingAfterUpdate, '0', 2) === -1) {
                    $v->errors()->add('amount', 'Updated payment amount exceeds the remaining invoice balance.');
                }
            }
        });
    }
}
