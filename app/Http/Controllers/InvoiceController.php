<?php

namespace App\Http\Controllers;

use Modules\Core\Entities\Booking;
use Barryvdh\DomPDF\Facade\Pdf; // If you're not using the global view() helper
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class InvoiceController extends Controller
{
    public function print(Booking $booking)
    {
        // Check if admin is logged in
        if (! Auth::guard('web')->check()) {
            abort(403, 'You are not authorized to access this resource');
        }

        // Eager load related data you might need for the invoice
        $booking->load(['customer', 'rooms', 'invoice.items', 'transaction']);

        // Always show the invoice view, regardless of whether a formal invoice exists
        return view('invoices.print', ['booking' => $booking]);
    }

    /**
     * Show invoice for customer
     */
    public function customerInvoice(Booking $booking)
    {
        $customer = Auth::guard('customer')->user();
        $user = Auth::user();
        $booking = Booking::find($booking->id);

        if (! $booking) {
            abort(404, 'Booking not found');
        }

        if (! $customer && ! $user) {
            abort(403, 'You must be logged in to view this invoice');
        }

        if ($booking->customer_id !== $customer?->id && ! $user) {
            abort(403, 'You are not authorized to view this invoice');
        }

        // Eager load related data for the invoice
        $booking->load(['customer', 'rooms', 'transaction', 'childrenPersons']);

        // Always show the invoice/receipt view, regardless of whether a formal invoice exists
        return view('invoices.customer', ['booking' => $booking]);
    }

    /**
     * Download invoice as PDF
     */
    public function downloadInvoicePdf(Booking $booking)
    {
        $customer = Auth::guard('customer')->user();
        $user = Auth::user();
        $booking = Booking::find($booking->id);

        if (! $booking) {
            abort(404, 'Booking not found');
        }

        if (! $customer && ! $user) {
            abort(403, 'You must be logged in to view this invoice');
        }

        if ($booking->customer_id !== $customer?->id && ! $user) {
            abort(403, 'You are not authorized to view this invoice');
        }

        // Eager load related data for the invoice
        $booking->load(['customer', 'rooms', 'transaction', 'childrenPersons']);

        // Generate PDF
        $pdf = PDF::loadView('invoices.customer', ['booking' => $booking]);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        // Return the PDF as a download
        return $pdf->download("invoice_{$booking->booking_number}.pdf");
    }
}
