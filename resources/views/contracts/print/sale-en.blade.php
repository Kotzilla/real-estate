{{-- Agreement to sell and purchase, English copy. Variables come from contracts/print. The transfer itself is done and
     registered at the Land Office; this agreement binds both sides until then. --}}
@php
  $balance = max(0, (float) $contract->price - (float) $contract->deposit);
@endphp
<h1>Agreement to Sell and Purchase</h1>
<div class="code">{{ $contract->code }}</div>

<p class="right">Date {{ $date($contract->signed_on) }}</p>

<div class="party">
  <p>This Agreement is made between <b>{{ $name($owner) }}</b></p>
  <p>ID / Passport No. <b>{{ $owner?->documentNo() ?? '-' }}</b>@if ($owner?->nationality), Nationality <b>{{ $owner->nationality }}</b>@endif</p>
  <p>Residing at <b>{{ $owner?->addressIn('en') ?? '-' }}</b>, Tel. {{ $owner?->phone ?? '-' }}</p>
  <p>(hereinafter called the "<b>Seller</b>")</p>
</div>

<div class="party">
  <p>And <b>{{ $name($customer) }}</b></p>
  <p>ID / Passport No. <b>{{ $customer->documentNo() ?? '-' }}</b>@if ($customer->nationality), Nationality <b>{{ $customer->nationality }}</b>@endif</p>
  <p>Residing at <b>{{ $customer->addressIn('en') ?? '-' }}</b>, Tel. {{ $customer->phone ?? '-' }}</p>
  <p>(hereinafter called the "<b>Buyer</b>")</p>
</div>

<p>The parties agree as follows:</p>

<ol>
  <li>
    <b>The property.</b> The Seller agrees to sell and the Buyer agrees to buy the {{ strtolower($property->type->label()) }}
    <b>{{ $property->title('en') }}</b>, {{ $property->fullAddress('en') }}@if ($property->land_area), land area {{ number_format((float) $property->land_area) }} m²@endif
    @if ($property->area), building area {{ number_format((float) $property->area) }} m²@endif@if ($property->is_furnished), with the furniture and fittings listed in the inventory @endif
    (the "<b>Property</b>"). The Seller warrants being its lawful owner, with the right to sell it.
  </li>
  <li>
    <b>Price.</b> The purchase price is <b>{{ $money($contract->price) }}</b> Baht ({{ $words($contract->price) }}).
  </li>
  <li>
    <b>Payment.</b> On signing this Agreement the Buyer pays a deposit of <b>{{ $money($contract->deposit) }}</b> Baht ({{ $words($contract->deposit) }}),
    which forms part of the price; the Seller's signature acknowledges receiving it. The balance of <b>{{ $money($balance) }}</b> Baht
    ({{ $words($balance) }}) is paid on the transfer date at the Land Office, against the transfer of ownership.
  </li>
  <li>
    <b>Transfer.</b> Ownership is transferred and registered at the Land Office of {{ $court }} Province on or before
    <b>{{ $date($contract->end_on) }}</b>, or on another date both parties agree in writing. The Seller hands over the title deed free of any mortgage,
    lease, lien or other encumbrance, and vacant possession of the Property on that day.
  </li>
  <li>
    <b>Fees and taxes.</b> The transfer fee is shared equally by the Seller and the Buyer. The Seller pays the specific business tax or stamp duty and
    the withholding tax. Each party pays its own legal and other advisers.
  </li>
  <li>
    Until the transfer the Seller keeps the Property in its present condition, pays all utilities, common area fees and taxes up to the transfer date,
    and does not sell, lease, mortgage or otherwise encumber it. The Buyer has inspected the Property and accepts it in its present condition.
  </li>
  <li>
    A foreign Buyer of a condominium unit remits the price from abroad in a foreign currency and obtains the Foreign Exchange Transaction form from the
    receiving bank, as the Land Office requires.
  </li>
  <li>
    <b>Buyer's default.</b> If the Buyer fails to pay the balance or attend the transfer, the Seller may terminate this Agreement by written notice and
    keep the deposit as liquidated damages.
  </li>
  <li>
    <b>Seller's default.</b> If the Seller fails to transfer the Property as agreed, the Buyer may, after {{ \App\Models\Contract::SALE_DEFAULT_DAYS }} days' written notice,
    terminate this Agreement; the Seller then returns the deposit together with an equal amount as liquidated damages.
  </li>
  @if ($contract->terms)
    <li><b>Further terms.</b> <span style="white-space: pre-line">{{ $contract->terms }}</span></li>
  @endif
  <li>
    Any change to this Agreement is made in writing and signed by both parties. This Agreement is governed by Thai law, under the jurisdiction of the
    courts of {{ $court }} Province.
  </li>
</ol>

<p>
  This Agreement is made in two copies of identical wording. Both parties have read and understood it, agree to comply with it, and keep one signed
  copy each.
</p>

<div class="signatures">
  <div><div class="line">Seller</div><b>{{ $name($owner) }}</b></div>
  <div><div class="line">Buyer</div><b>{{ $name($customer) }}</b></div>
  <div><div class="line">Witness</div><b>{{ mb_strtoupper($contract->witness?->name ?? $contract->agent?->name ?? '') }}</b></div>
</div>
