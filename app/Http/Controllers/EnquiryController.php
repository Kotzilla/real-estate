<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnquiryStatusRequest;
use App\Models\Enquiry;
use App\Traits\LogActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Enquiry Controller
 *
 * An enquiry's page in the back office: who asked about what, its status (open → in progress → closed) and the
 * staff's follow-up notes (the `comments` Livewire component). The list is App\Livewire\Enquiries\Table; routes are
 * gated on App\Policies\EnquiryPolicy.
 *
 * @package App\Controllers
 */
class EnquiryController extends Controller
{
    use LogActivity;

    /**
     * An enquiry's page
     *
     * @param  Enquiry  $enquiry
     * @return View
     */
    public function show(Enquiry $enquiry): View
    {
        return view('enquiries.show', ['enquiry' => $enquiry->load('enquirable')]);
    }

    /**
     * Move the enquiry to another status
     *
     * @param  EnquiryStatusRequest  $request
     * @param  Enquiry  $enquiry
     * @return RedirectResponse
     */
    public function update(EnquiryStatusRequest $request, Enquiry $enquiry): RedirectResponse
    {
        $enquiry->update($request->validated());
        $this->log($enquiry, 'enquiry-update');

        return back()->with('success', __('enquiries.updated', ['status' => $enquiry->status->label()]));
    }
}
