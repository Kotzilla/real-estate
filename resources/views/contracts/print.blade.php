{{-- The printable contract: A4, one language per copy (?lang en | th). A lease or an agreement to sell and purchase,
     by the contract's type. Standalone: no back-office chrome; the toolbar hides when printing. --}}
@php
  $property = $contract->property;
  $owner = $property->owner;
  $customer = $contract->customer;
  $money = fn ($amount) => number_format((float) $amount);
  $words = fn ($amount) => \App\Models\Contract::inWords($amount, $lang);
  $date = fn ($value) => \App\Models\Contract::dateIn($value, $lang);
  $name = fn ($person) => $person ? mb_strtoupper($person->fullName($lang)) : '-';
  $court = $lang === 'th' ? ($property->province?->name_th ?? '-') : ($property->province?->name ?? '-');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>{{ $contract->code }} · {{ $customer->fullName($lang) }}</title>
  @vite('resources/css/app.css')
  <style>
    @page { size: A4; margin: 18mm 16mm; }
    body { margin: 0; background: #e5e7eb; color: #111827; font-size: 14px; line-height: 1.65; }
    .sheet { box-sizing: border-box; max-width: 210mm; margin: 24px auto; padding: 18mm 16mm; background: #fff; box-shadow: 0 2px 12px rgb(0 0 0 / .12); }
    .toolbar { position: sticky; top: 0; display: flex; gap: 8px; justify-content: center; padding: 10px; background: #12171f; }
    .toolbar a, .toolbar button { font: inherit; font-size: 13px; padding: 6px 14px; border-radius: 6px; border: 0; cursor: pointer; text-decoration: none; background: #1d2a40; color: #fff; }
    .toolbar .primary { background: #c1df0f; color: #12171f; font-weight: 600; }
    h1 { margin: 0 0 4px; text-align: center; font-size: 22px; }
    .code { text-align: center; font-size: 12px; color: #6b7280; margin-bottom: 18px; }
    .right { text-align: right; }
    .party { margin: 14px 0; }
    .party p { margin: 2px 0; }
    ol { padding-left: 22px; }
    ol > li { margin-bottom: 10px; }
    ul { padding-left: 20px; margin: 4px 0; }
    b { font-weight: 600; }
    .signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 56px; text-align: center; font-size: 13px; }
    .signatures .line { border-top: 1px dotted #111827; margin-top: 48px; padding-top: 6px; }
    @media print {
      body { background: #fff; }
      .toolbar { display: none; }
      .sheet { margin: 0; padding: 0; max-width: none; box-shadow: none; }
    }
  </style>
</head>
<body>
  <div class="toolbar">
    <button type="button" class="primary" onclick="window.print()">@lang('contracts.print.print')</button>
    <a href="{{ route('backend.contracts.print', [$contract, $lang === 'th' ? 'en' : 'th']) }}">{{ $lang === 'th' ? 'English' : 'ไทย' }}</a>
    <a href="{{ route('backend.contracts.show', $contract) }}">@lang('contracts.print.back')</a>
  </div>

  <div class="sheet">
    @include('contracts.print.'.$contract->type->value.'-'.$lang)
  </div>
</body>
</html>
