<?php

namespace Modules\PaymentManagement\Http\Requests\Api\V1\Payments;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Gate;
use Modules\PaymentManagement\Models\Payment;
use Modules\PaymentManagement\Models\Invoice;
use Illuminate\Contracts\Validation\Validator;

class StorePaymentRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Payment::class);
    }

    public function rules(): array
    {
        return [
            'invoice_id' => [
                'required',
                'integer',
                'exists:invoices,id',
            ],
            'payment_method_id' => [
                'nullable',
                'integer',
                'exists:payment_methods,id',
            ],
            'amount' => [
                'required',
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
            'invoice_id.required' => 'Invoice is required.',
            'invoice_id.integer'  => 'Invoice ID must be a valid integer.',
            'invoice_id.exists'   => 'The selected invoice does not exist.',
            'payment_method_id.integer' => 'Payment method ID must be a valid integer.',
            'payment_method_id.exists'  => 'The selected payment method does not exist.',
            'amount.required' => 'Payment amount is required.',
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

            if (! $this->has('invoice_id') || ! $this->has('amount')) {
                return;
            }

            $invoice = Invoice::with('payments')->find($this->invoice_id);

            if (! $invoice) {
                $v->errors()->add('invoice_id', 'Invoice not found.');
                return;
            }

            if ($invoice->status === 'paid') {
                $v->errors()->add('invoice_id', 'Invoice is already paid.');
                return;
            }

            if ($invoice->status === 'cancelled' ) {
                $v->errors()->add('invoice_id', 'Invoice is not payable.');
                return;
            }

            $paidAmount = (string) $invoice->payments()->sum('amount');
            $totalAmount = (string) $invoice->tot_amount;
            $paymentAmount = (string) $this->amount;

            $remaining = bcsub($totalAmount, $paidAmount, 2);
            $remainingAfterPayment = bcsub($remaining, $paymentAmount, 2);

            if (bccomp($remainingAfterPayment, '0', 2) === -1) {
                $v->errors()->add('amount', 'Payment amount exceeds the remaining invoice balance.');
            }
        });
    }
}
