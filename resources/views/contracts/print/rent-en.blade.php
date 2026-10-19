{{-- Lease agreement, English copy (from findnest's). Variables come from contracts/print. --}}
<h1>Lease Agreement</h1>
<div class="code">{{ $contract->code }}</div>

<p class="right">Made at {{ $property->title('en') }}@if ($property->floor), {{ $property->floor }}th floor @endif</p>
<p class="right">Date {{ $date($contract->signed_on) }}</p>

<div class="party">
  <p>This Agreement is made between <b>{{ $name($owner) }}</b></p>
  <p>ID / Passport No. <b>{{ $owner?->documentNo() ?? '-' }}</b>@if ($owner?->nationality), Nationality <b>{{ $owner->nationality }}</b>@endif</p>
  <p>Residing at <b>{{ $owner?->addressIn('en') ?? '-' }}</b></p>
  <p>@if ($owner?->birth_on)Age {{ (int) $owner->birth_on->diffInYears($contract->signed_on) }} years, @endif Tel. {{ $owner?->phone ?? '-' }}, Email {{ $owner?->email ?? '-' }}</p>
  <p>(hereinafter called the "<b>Lessor</b>")</p>
</div>

<div class="party">
  <p>And <b>{{ $name($customer) }}</b></p>
  <p>ID / Passport No. <b>{{ $customer->documentNo() ?? '-' }}</b>@if ($customer->nationality), Nationality <b>{{ $customer->nationality }}</b>@endif</p>
  @if ($customer->address)
    <p>Residing at <b>{{ $customer->addressIn('en') }}</b></p>
  @endif
  <p>@if ($customer->birth_on)Age {{ (int) $customer->birth_on->diffInYears($contract->signed_on) }} years, @endif Tel. {{ $customer->phone ?? '-' }}, Email {{ $customer->email ?? '-' }}</p>
  <p>(hereinafter called the "<b>Lessee</b>")</p>
</div>

<p>The parties agree to the following terms and conditions:</p>

<ol>
  <li>
    The Lessor agrees to let and the Lessee agrees to rent the {{ strtolower($property->type->label()) }}
    <b>{{ $property->title('en') }}</b>, {{ $property->fullAddress('en') }}@if ($property->is_furnished), with the furniture and appliances listed in the inventory @endif
    (the "<b>Premises</b>"), for residential use only. The rent is <b>{{ $money($contract->price) }}</b> Baht per month ({{ $words($contract->price) }}),
    including the common area maintenance fee.
  </li>
  <li>
    On signing this Agreement the Lessee pays the Lessor a security deposit of <b>{{ $money($contract->deposit) }}</b> Baht ({{ $words($contract->deposit) }})
    @if ((float) $contract->advance > 0)
      and <b>{{ $money($contract->advance) }}</b> Baht ({{ $words($contract->advance) }}) as rent in advance, a total of
      <b>{{ $money($contract->dueOnSigning()) }}</b> Baht ({{ $words($contract->dueOnSigning()) }})
    @endif.
  </li>
  <li>
    The term of this Agreement is <b>{{ $contract->months }} months</b>, from <b>{{ $date($contract->start_on) }}</b> to <b>{{ $date($contract->end_on) }}</b>.
  </li>
  <li>
    <b>Rent payments.</b> The Lessee pays the rent in advance on or before day <b>{{ $contract->payment_day }}</b> of each month until this Agreement ends,
    by transfer to the Lessor's account:
    <ul>
      <li>Bank: {{ $owner?->bank_name ?? '-' }}</li>
      <li>Account name: {{ $owner?->bank_account_name ?? '-' }}</li>
      <li>Account no.: {{ $owner?->bank_account_no ?? '-' }}</li>
    </ul>
    Rent more than {{ \App\Models\Contract::LATE_GRACE_DAYS }} days late is charged a fine of {{ $money(\App\Models\Contract::LATE_FEE_PER_DAY) }} Baht per day.
    The Lessor may terminate this Agreement if rent is more than two weeks overdue; the security deposit is then forfeited to the Lessor,
    and the Lessee allows the Lessor to remove the Lessee's belongings from the Premises and change the locks.
  </li>
  <li>
    The security deposit secures the Lessee's obligations and covers damage to the Premises; it is not rent. It is refunded within
    {{ \App\Models\Contract::DEPOSIT_REFUND_DAYS }} days after this Agreement ends, after a final inspection and less unpaid bills, the cost of repairing damage
    beyond normal wear and tear, and cleaning: general cleaning {{ $money(\App\Models\Contract::CLEANING_FEE_GENERAL) }} Baht, bed sheets
    {{ $money(\App\Models\Contract::CLEANING_FEE_BEDSHEET) }} Baht per set, air conditioners {{ $money(\App\Models\Contract::CLEANING_FEE_AIRCON) }} Baht per unit.
    The Lessee has the air conditioners cleaned every six months. Ending this Agreement early forfeits the deposit.
  </li>
  <li>
    The Lessee pays electricity, water, internet and any other charges for the Premises during the term, by the bills or meters.
  </li>
  <li>
    The Lessee is responsible for loss of or damage to the Premises, furniture, fixtures and appliances, and looks after the Premises as a careful
    owner would; normal wear and tear excepted.
  </li>
  <li>
    The Lessee may not keep or sell anything illegal on the Premises, and removes at the Lessee's cost anything the Lessor reasonably considers
    improper to keep there. The Lessor is not responsible for loss of or damage to the Lessee's belongings.
  </li>
  <li>
    The Lessee may not assign or sub-let all or part of the Premises without the Lessor's written consent, which is not unreasonably withheld,
    nor use the Premises for any unlawful, immoral or disturbing purpose.
  </li>
  <li>
    The Lessee makes no alteration to the Premises without the Lessor's written consent. Permanent alterations become the Lessor's property;
    temporary ones are removed by the Lessee, at the Lessee's cost, when this Agreement ends.
  </li>
  <li>
    <b>Inspection.</b> The Lessee lets the Lessor or the Lessor's representative inspect the Premises at a time convenient to the Lessee, with at
    least {{ \App\Models\Contract::INSPECTION_NOTICE_HOURS }} hours' notice, Monday to Friday 10:00–17:00, to check its condition, carry out necessary repairs and
    maintenance, and comply with the rules of the building or village and any notice from the authorities.
  </li>
  <li>
    <b>Small repairs.</b> The Lessee pays for small repairs (light bulbs, leaking taps…) up to {{ $money(\App\Models\Contract::SMALL_REPAIR_CAP) }} Baht each time,
    except in the first month. The Lessor repairs damage to the building or its contents caused by natural events such as floods or earthquakes.
  </li>
  <li>
    <b>Renewal.</b> Either party who does not wish to renew gives written notice at least {{ \App\Models\Contract::RENEWAL_NOTICE_DAYS }} days before the end of the
    term. The Lessee may ask in writing, at least {{ \App\Models\Contract::RENEWAL_NOTICE_DAYS }} days before the end, to renew for up to 12 further months on terms
    agreed by both parties.
  </li>
  <li>
    If the Lessee breaches this Agreement, or when it ends for any reason, the Lessee moves out with all belongings and occupants within
    {{ \App\Models\Contract::MOVE_OUT_DAYS }} days; the Lessor may keep the deposit and take back the Premises.
  </li>
  <li>
    {{ $property->is_pet ? 'Pets are allowed with the Lessor\'s prior consent; the Lessee pays for any damage they cause.' : 'No pets are allowed on the Premises.' }}
    No smoking inside. The Lessee follows the rules and by-laws of the building, juristic person or management office.
  </li>
  <li>
    The Lessor notifies the immigration office of a foreign Lessee's residence (Form TM.30) at the first check-in; later notifications are the
    Lessee's responsibility. Check-in and check-out are between 09:00 and 17:00.
  </li>
  @if ($contract->terms)
    <li><b>Further terms.</b> <span style="white-space: pre-line">{{ $contract->terms }}</span></li>
  @endif
  <li>
    Any change to this Agreement is made in writing and signed by both parties. A dispute is first put to a neutral mediator agreed by both parties;
    this Agreement is governed by Thai law, under the jurisdiction of the courts of {{ $court }} Province.
  </li>
</ol>

<p>
  This Agreement is made in two copies of identical wording. Both parties have read and understood it, agree to comply with it, and keep one signed
  copy each.
</p>

<div class="signatures">
  <div><div class="line">Lessor</div><b>{{ $name($owner) }}</b></div>
  <div><div class="line">Lessee</div><b>{{ $name($customer) }}</b></div>
  <div><div class="line">Witness</div><b>{{ mb_strtoupper($contract->witness?->name ?? $contract->agent?->name ?? '') }}</b></div>
</div>
