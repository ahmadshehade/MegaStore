<?php

namespace Modules\PaymentManagement\Http\Requests\Api\V1\Payments;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Gate;
use Modules\PaymentManagement\Models\Payment;
use Modules\PaymentManagement\Models\Invoice;
use Illuminate\Contracts\Validation\Validator;
use Modules\PaymentManagement\Models\PaymentMethod;

class StorePaymentRequest extends BaseRequest
{

    /**
     * Summary of authorize
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Payment::class);
    }

    /**
     * Summary of rules
     * @return array{amount: string[], currency: string[], invoice_id: string[], payment_date: string[], payment_method_id: string[], payment_notes: string[]}
     */
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

    /**
     * Summary of messages
     * @return array{amount.gt: string, amount.numeric: string, amount.regex: string, amount.required: string, currency.size: string, currency.string: string, invoice_id.exists: string, invoice_id.integer: string, invoice_id.required: string, payment_date.before_or_equal: string, payment_date.date: string, payment_method_id.exists: string, payment_method_id.integer: string, payment_notes.max: string, payment_notes.string: string}
     */
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

    /**
     * Summary of withValidator
     * @param Validator $validator
     * @return void
     */
    protected function withValidator(Validator $validator): void
    {
        $validator->after(function ($v) {
            if (! $this->has('invoice_id') || ! $this->has('amount')) {
                return;
            }

            $scale = 2;

            $paymentMethod = null;
            $fee = '0.00';
            if ($this->filled('payment_method_id')) {
                $paymentMethod = PaymentMethod::find($this->payment_method_id);
                if (! $paymentMethod) {
                    $v->errors()->add('payment_method_id', 'Invalid payment method.');
                    return;
                }

                $fee = number_format($paymentMethod->fee, $scale, '.', '');
            }

            $invoice = Invoice::with('payments')->find($this->invoice_id);
            if (! $invoice) {
                $v->errors()->add('invoice_id', 'Invoice not found.');
                return;
            }

            if (in_array($invoice->status, ['paid', 'cancelled', 'revised'])) {
                $v->errors()->add(
                    'invoice_id',
                    'This invoice cannot be paid. Current status: ' . $invoice->status . '.'
                );
                return;
            }

            $paidAmount  = (string) $invoice->payments()->sum('amount');
            $totalAmount = (string) $invoice->tot_amount;
            $remaining   = bcsub($totalAmount, $paidAmount, $scale);

            // Invoice already fully paid
            if (bccomp($remaining, '0', $scale) <= 0) {
                $v->errors()->add('invoice_id', 'This invoice has already been fully paid.');
                return;
            }

            $grossInput = number_format($this->amount, $scale, '.', '');

            if (bccomp($grossInput, $fee, $scale) <= 0) {
                $v->errors()->add(
                    'amount',
                    'The payment amount must be greater than the payment method fee (' . $fee . ').'
                );
                return;
            }


            if (bccomp($remaining, $fee, $scale) <= 0) {
                $requiredGross = bcadd($remaining, $fee, $scale);

                if (bccomp($grossInput, $requiredGross, $scale) !== 0) {
                    $v->errors()->add('amount', sprintf(
                        'Only %s remains on this invoice and the fee is %s. You must send exactly %s (gross amount) to complete the payment.',
                        $remaining,
                        $fee,
                        $requiredGross
                    ));
                    return;
                }

                return;
            }

            $net = bcsub($grossInput, $fee, $scale);

            if (bccomp($net, $remaining, $scale) === 1) {
                $maxAllowedGross = bcadd($remaining, $fee, $scale);

                $v->errors()->add('amount', sprintf(
                    'The sent amount (%s) results in a net payment (%s) that exceeds the remaining invoice balance (%s). The maximum allowed gross amount is %s (remaining %s + fee %s).',
                    $grossInput,
                    $net,
                    $remaining,
                    $maxAllowedGross,
                    $remaining,
                    $fee
                ));
                return;
            }
        });
    }
}
