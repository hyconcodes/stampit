<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function invoicesUpload(Request $request)
    {
        $request->validate([
            'rrr' => 'required|digits:12|unique:invoices,rrr',
            'amount' => 'required|numeric|min:0',
            'payment_id' => 'required|string|exists:payments,id',
            'invoice' => 'required|file|mimes:pdf|max:2048',
            'caption' => 'string|max:255',
        ], [
            'rrr.unique' => 'This RRR has already been used',
            'invoice.required' => 'Invoice file is required.',
            'invoice.mimes' => 'Invoice file must be a PDF.',
            'invoice.max' => 'Invoice file size must not exceed 2MB.',
            'amount.min' => 'Amount must be greater than or equal to 0.',
            'payment_id.exists' => 'Payment Type does not exist.',
            'caption.max' => 'Caption must not exceed 255 characters.',
            'rrr.digit' => 'RRR must be exactly 12 digits.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.required' => 'Amount is required.',
            'payment_id.required' => 'Payment Type is required; e.g - school fees or igr, etc.',
        ]);
        $invoice = $request->file('invoice');
        $invoiceName = time() . '_' . $invoice->getClientOriginalName();
        $invoice->move(public_path('invoices'), $invoiceName);
        $invoicePath = 'invoices/' . $invoiceName;
        $invoice = Invoice::create([
            'user_id' => auth()->user()->id,
            'rrr' => $request->rrr,
            'amount' => $request->amount,
            'payment_id' => $request->payment_id,
            'invoice' => $invoicePath,
            'caption' => $request->caption,
        ]);
        return response()->json([
            'message' => 'Invoice uploaded successfully, waiting for approval.',
            'invoice' => $invoice,
        ], 201);
    }

    // list all invoices based order by created_at use paginate of 5, whenever the user press the button load more, it will load 5 more invoices
    public function invoicesList()
    {
        $invoices = Invoice::where('user_id', auth()->user()->id)->orderBy('created_at', 'desc')->paginate(5);
        return response()->json($invoices, 200);
    }

    public function invoicesDelete($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            if ($invoice->user_id !== auth()->user()->id) {
                return response()->json(['message' => 'You are not authorized to delete this invoice.'], 403);
            }
            $invoice->delete();
            return response()->json(['message' => 'Invoice deleted successfully.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the invoice.', 'error' => $e->getMessage()], 500);
        }
    }
    // update invoice
    public function invoicesUpdate($id, Request $request)
    {
        try {
            $request->validate([
                'rrr' => 'nullable|digits:12',
                'amount' => 'nullable|numeric|min:0',
                'payment_id' => 'nullable|string|exists:payments,id',
                'invoice' => 'nullable|file|mimes:pdf|max:2048',
                'caption' => 'nullable|string|max:255',
            ]);

            $invoice = Invoice::findOrFail($id);

            // Check if the user is authorized to update the invoice
            if ($invoice->user_id !== auth()->user()->id) {
                return response()->json(['message' => 'You are not authorized to update this invoice.'], 403);
            }

            // Handle file upload if present
            if ($request->hasFile('invoice')) {
                $oldInvoicePath = public_path($invoice->invoice);
                if (file_exists($oldInvoicePath)) {
                    unlink($oldInvoicePath);
                }
                $newInvoice = $request->file('invoice');
                $newInvoiceName = time() . '_' . $newInvoice->getClientOriginalName();
                $newInvoice->move(public_path('invoices'), $newInvoiceName);
                $invoice->invoice = 'invoices/' . $newInvoiceName;
            }

            // Update other fields if they are present
            if ($request->filled('rrr')) {
                $invoice->rrr = $request->rrr;
            }
            if ($request->filled('amount')) {
                $invoice->amount = $request->amount;
            }
            if ($request->filled('payment_id')) {
                $invoice->payment_id = $request->payment_id;
            }
            if ($request->filled('caption')) {
                $invoice->caption = $request->caption;
            }

            $invoice->save();

            return response()->json(['message' => 'Invoice updated successfully.', 'invoice' => $invoice], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the invoice.', 'error' => $e->getMessage()], 500);
        }
    }
    // get invoice by id
    public function invoicesGet($id)
    {
        $invoice = Invoice::where('id', $id)->where('user_id', auth()->user()->id)->first();
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found or you are not authorized to view it.'], 404);
        }
        return response()->json($invoice, 200);
    }
    // get invoice by rrr
    public function invoicesGetByRrr($rrr)
    {
        $invoice = Invoice::where('user_id', auth()->user()->id)->where('rrr', $rrr)->first();
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found.'], 404);
        }
        return response()->json($invoice, 200);
    }
    // query invoice by date
    public function invoicesQueryByDate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $invoices = Invoice::where('user_id', auth()->user()->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate(5);
        return response()->json($invoices, 200);
    }
    // get all payment
    public function getAllPayments()
    {
        $payments = Payment::all();
        return response()->json($payments, 200);
    }
}
