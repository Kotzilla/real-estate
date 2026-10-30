import $ from 'jquery';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import Noty from 'noty';
import 'noty/lib/noty.css';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { Thai } from 'flatpickr/dist/l10n/th.js';
import Tagify from '@yaireo/tagify';
import './limited-textarea';

window.$ = window.jQuery = $;
window.Swal = Swal;
window.Noty = Noty;
window.Tagify = Tagify;

// Calendars in the page's language: Thai month / weekday names on a Thai page (English is flatpickr's default)
if (document.documentElement.lang === 'th') flatpickr.localize(Thai);

// One toast helper for everything: Livewire `toast` events and flashed session messages.
window.toast = (type, text) => new Noty({
  type: ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info',
  text,
  theme: 'relax',
  layout: 'topRight',
  timeout: 3500,
  progressBar: true,
  closeWith: ['click', 'button'],
}).show();

// <form onsubmit="return confirmSubmit(event, { title, text, confirm, cancel })">: ask first, then submit the form for real.
window.confirmSubmit = function (event, { title, text, confirm, cancel }) {
  const form = event.target;
  Swal.fire({
    title,
    text,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#e11d48',
    cancelButtonColor: '#64748b',
    confirmButtonText: confirm,
    cancelButtonText: cancel,
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) form.submit(); // submit() skips onsubmit, so no loop
  });

  return false;
};


const handleToast = (event) => {
  const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;

  if (typeof detail === 'string') {
    window.toast('success', detail);
  } else if (detail) {
    window.toast(detail.type || 'success', detail.text || detail.message || '');
  }
};

window.addEventListener('toast', handleToast);

// Ensure KTUI dropdowns work seamlessly across Livewire morphs and dynamic renders.
// 1. Capture phase listener ensures the KTDropdown instance exists before KTUI's delegated click handler checks for it.
document.addEventListener('click', (event) => {
  const toggle = event.target.closest('[data-kt-dropdown-toggle]');
  if (toggle && window.KTDropdown) {
    const container = toggle.closest('[data-kt-dropdown]');
    if (container && !window.KTDropdown.getInstance(container)) {
      window.KTDropdown.getOrCreateInstance(container);
    }
  }
  const selectDisplay = event.target.closest('[data-kt-select-display]');
  if (selectDisplay) {
    const wrapper = selectDisplay.closest('.kt-select-wrapper');
    const select = wrapper?.previousElementSibling;
    if (select && select.instance && typeof window.__syncColorOptions === 'function') {
      window.__syncColorOptions(select.instance);
    }
  }
}, true);

// 2. Re-initialize dropdown and tooltip instances when Livewire morphs the DOM (e.g. searching, filtering, paginating):
//    KTUI only initialises what is on the page at load, so icons Livewire renders later would get no tooltip.
//    'morphed' fires once per component update; 'morph.updated' fired per element, so a long table rescanned the
//    whole page thousands of times per click.
const initKtDropdownsOnLivewire = () => {
  if (window.Livewire) {
    window.Livewire.hook('morphed', () => {
      window.KTDropdown?.init();
      window.KTTooltip?.init();
    });
  }
};
if (window.Livewire) {
  initKtDropdownsOnLivewire();
} else {
  document.addEventListener('livewire:init', initKtDropdownsOnLivewire);
}

// 3. Session expired (419) on a Livewire request: our styled dialog instead of Livewire's browser confirm().
//    Cancel keeps the page open so typed text can be copied; nothing is re-sent, the reload fetches a fresh token.
const handlePageExpiryOnLivewire = () => {
  window.Livewire.hook('request', ({ fail }) => {
    fail(({ status, preventDefault }) => {
      if (status !== 419) return;
      preventDefault();
      const t = window.__pageExpired;
      Swal.fire({
        title: t.title,
        text: t.text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: t.reload,
        cancelButtonText: t.cancel,
        cancelButtonColor: '#64748b',
        reverseButtons: true,
      }).then((result) => {
        if (result.isConfirmed) window.location.reload();
      });
    });
  });
};
if (window.Livewire) {
  handlePageExpiryOnLivewire();
} else {
  document.addEventListener('livewire:init', handlePageExpiryOnLivewire);
}


// Form widgets inside Livewire views. Put them in a `wire:ignore` wrapper so re-renders leave the widget's DOM
// alone; the native <input>/<select> inside keeps its `wire:model` and gets the widget's input/change event.
const initAlpineComponents = () => {
  if (!window.Alpine || window.Alpine.__storeComponentsRegistered) return;
  window.Alpine.__storeComponentsRegistered = true;

  // <div wire:ignore x-data="datePicker"><input x-ref="input" wire:model="…" class="kt-input"></div>
  window.Alpine.data('datePicker', () => ({
    fp: null,
    init() {
      this.fp = flatpickr(this.$refs.input, {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd M Y',
        allowInput: true,
        appendTo: document.body, // the drawer body scrolls and would clip the calendar
      });
    },
    destroy() {
      this.fp?.destroy();
    },
  }));

  // Tagify input: wraps an input with @yaireo/tagify for tag chips and autocomplete suggestions.
  // <div wire:ignore x-data="tagInput({ whitelist: @js($allTags ?? []) })">
  //   <input x-ref="input" name="tags" ...>
  // </div>
  window.Alpine.data('tagInput', (config = {}) => ({
    tagify: null,
    submitHandler: null,
    init() {
      const input = this.$refs.input || this.$el.querySelector('input');
      if (!input) return;
      if (input.__tagify) {
        this.tagify = input.__tagify;
        return;
      }

      const opts = {
        whitelist: Array.isArray(config.whitelist) ? config.whitelist : [],
        enforceWhitelist: false,
        skipInvalid: true,
        trim: true,
        dropdown: {
          maxItems: 25,
          classname: 'kt-tagify-dropdown',
          enabled: 0,
          closeOnSelect: false,
        },
        originalInputValueFormat: (valuesArr) => valuesArr.map((item) => item.value).join(', '),
        ...config,
      };

      this.tagify = new Tagify(input, opts);
      input.__tagify = this.tagify;

      // When the form is submitted, if the user typed text without hitting Enter or blurring,
      // convert that text into a tag so it gets saved.
      const form = input.closest('form');
      if (form) {
        this.submitHandler = () => {
          if (this.tagify?.DOM?.input) {
            const raw = this.tagify.DOM.input.textContent?.trim();
            if (raw) {
              this.tagify.addTags(raw);
            }
            input.value = this.tagify.getInputValue();
          }
        };
        form.addEventListener('submit', this.submitHandler);
      }
    },
    destroy() {
      if (this.submitHandler && this.$refs.input) {
        const form = this.$refs.input.closest('form');
        form?.removeEventListener('submit', this.submitHandler);
      }
      this.tagify?.destroy();
    },
  }));

  // Date range bound to two Livewire properties (Y-m-d), e.g. a list's issued-from / issued-to filter; clearing the dates
  // or the component's Reset empties both. `labels` ({ today: 'วันนี้', … }) adds those quick ranges beside the calendar
  // (like elib's daterangepicker; KTUI has no date picker). <div wire:ignore x-data="dateRangePicker('from', 'to', @js([...labels from office.filters.ranges.*]))">
  // <input x-ref="input" class="kt-input"></div>
  const day = (offset = 0) => { const d = new Date(); d.setHours(0, 0, 0, 0); d.setDate(d.getDate() + offset); return d; };
  const month = (offset, last = false) => { const d = day(); return new Date(d.getFullYear(), d.getMonth() + offset + (last ? 1 : 0), last ? 0 : 1); };
  const months = (offset) => { const d = day(); d.setMonth(d.getMonth() + offset); return d; }; // 31 May - 3 months rolls to 3 Mar
  const RANGES = {
    today: () => [day(), day()],
    yesterday: () => [day(-1), day(-1)],
    last_7_days: () => [day(-6), day()],
    this_month: () => [month(0), month(0, true)],
    last_month: () => [month(-1), month(-1, true)],
    last_3_months: () => [months(-3), day()], // rolling, like last_7_days: 28 Jun – 28 Sep
    last_6_months: () => [months(-6), day()],
  };

  window.Alpine.data('dateRangePicker', (from, to, labels = {}) => ({
    fp: null,
    init() {
      const ymd = (dates) => dates.map((d) => flatpickr.formatDate(d, 'Y-m-d'));
      // highlight the quick range the selection matches
      const mark = (fp) => fp.calendarContainer.querySelectorAll('[data-range]').forEach((btn) => {
        btn.classList.toggle('active', ymd(RANGES[btn.dataset.range]()).join() === ymd(fp.selectedDates).join());
      });
      this.fp = flatpickr(this.$refs.input, {
        onReady: (_, __, fp) => {
          const keys = Object.keys(RANGES).filter((key) => labels[key]);
          if (!keys.length) return;
          const list = document.createElement('div');
          list.className = 'flatpickr-ranges';
          keys.forEach((key) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.range = key;
            btn.textContent = labels[key];
            btn.addEventListener('click', () => { fp.setDate(RANGES[key](), true); fp.close(); });
            list.append(btn);
          });
          const main = document.createElement('div'); // the calendar itself, beside the list
          main.className = 'flatpickr-main';
          main.append(...fp.calendarContainer.childNodes);
          fp.calendarContainer.classList.add('has-ranges');
          fp.calendarContainer.append(list, main);
          mark(fp);
        },
        onOpen: (_, __, fp) => mark(fp),
        mode: 'range',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd M Y',
        appendTo: document.body,
        defaultDate: [this.$wire.get(from), this.$wire.get(to)].filter(Boolean),
        onChange: (dates, _, fp) => {
          mark(fp);
          if (dates.length === 1) return; // wait for the second click
          const [start = '', end = ''] = ymd(dates);
          this.$wire.$set(from, start, false);
          this.$wire.$set(to, end);
        },
      });
      // Reset (or the URL) changed the properties: show that range, or none
      const sync = () => {
        const dates = [this.$wire.get(from), this.$wire.get(to)].filter(Boolean);
        if (dates.join() !== ymd(this.fp.selectedDates).join()) this.fp.setDate(dates, false);
      };
      this.$watch(`$wire.${from}`, sync);
      this.$watch(`$wire.${to}`, sync);
    },
    destroy() {
      this.fp?.destroy();
    },
  }));

  // Photo picker for the issue form: checks type, size and count in the browser BEFORE uploading, then hands the
  // good files to Livewire (`newPhotos`), which checks them again on the server. `messages` come from lang/.
  // <div x-data="photoPicker({ max: 3, maxKb: 1024, types: [...], messages: {...} })"> … <input type="file" multiple @change="pick($event)">
  window.Alpine.data('photoPicker', (cfg) => ({
    error: '',
    uploading: false,
    pick(event) {
      const files = Array.from(event.target.files || []);
      event.target.value = ''; // allow picking the same file again
      this.error = '';

      const existingCount = Array.isArray(this.$wire.existingPhotos) ? this.$wire.existingPhotos.length : 0;
      const currentPhotos = Array.isArray(this.$wire.photos) ? this.$wire.photos.length : 0;
      let room = cfg.max - (currentPhotos + existingCount);
      const ok = [];
      for (const file of files) {
        if (!cfg.types.includes(file.type)) {
          this.error = cfg.messages.type.replace(':name', file.name);
        } else if (file.size > cfg.maxKb * 1024) {
          this.error = cfg.messages.size.replace(':name', file.name);
        } else if (room <= 0) {
          this.error = cfg.messages.max;
        } else {
          ok.push(file);
          room--;
        }
      }
      if (!ok.length) return;

      this.uploading = true;
      this.$wire.uploadMultiple('newPhotos', ok,
        () => { this.uploading = false; },
        () => { this.uploading = false; this.error = cfg.messages.upload; });
    },
  }));

  if (window.KTSelect && window.__ktSelectI18n) {
    KTSelect.config({
      placeholder: window.__ktSelectI18n.placeholder,
      label: window.__ktSelectI18n.placeholder,
      searchPlaceholder: window.__ktSelectI18n.searchPlaceholder,
      searchEmpty: window.__ktSelectI18n.searchEmpty,
    });
  }

  // Synchronize custom data-color attributes into KTSelect's optionsConfig and DOM elements
  const syncColorOptions = (instance) => {
    if (!instance || !instance._element) return;
    const select = instance._element;
    instance._config = instance._config || {};
    instance._config.optionsConfig = instance._config.optionsConfig || {};

    const nativeOptions = select.querySelectorAll('option');
    nativeOptions.forEach((opt) => {
      const val = opt.value;
      const color = opt.getAttribute('data-color') || opt.dataset.color || '';
      const hasColor = color ? 'inline-block' : 'none';
      instance._config.optionsConfig[val] = {
        ...(instance._config.optionsConfig[val] || {}),
        color,
        hasColor,
      };
    });

    // Style swatches inside the dropdown options list
    const dropdown = instance.getDropdownElement?.() || instance._dropdownContentElement;
    if (dropdown) {
      const items = dropdown.querySelectorAll('[data-kt-select-option]');
      items.forEach((li) => {
        const val = li.getAttribute('data-value') ?? '';
        const cfg = instance._config.optionsConfig[val];
        const swatch = li.querySelector('.kt-color-swatch');
        if (swatch) {
          if (cfg && cfg.color) {
            swatch.style.background = cfg.color;
            swatch.style.display = 'inline-block';
          } else {
            swatch.style.display = 'none';
          }
        }
      });
    }

    // Style swatch inside the selected display element
    const display = instance.getValueDisplayElement?.() || instance._displayElement;
    if (display) {
      const swatch = display.querySelector('.kt-color-swatch');
      if (swatch) {
        const selected = instance.getSelectedOptions?.();
        const selectedVal = (selected && selected.length > 0) ? selected[0] : (select.value ?? '');
        const cfg = instance._config.optionsConfig[selectedVal];
        if (cfg && cfg.color) {
          swatch.style.background = cfg.color;
          swatch.style.display = 'inline-block';
        } else {
          swatch.style.display = 'none';
        }
      }
    }
  };
  window.__syncColorOptions = syncColorOptions;

  // Patch KTSelect once so optionsConfig and swatches stay in sync across structure builds, rebuilding, and display updates
  if (window.KTSelect && !window.KTSelect.__colorSelectPatched) {
    window.KTSelect.__colorSelectPatched = true;

    const origCreateHtmlStructure = window.KTSelect.prototype._createHtmlStructure;
    window.KTSelect.prototype._createHtmlStructure = function () {
      origCreateHtmlStructure.call(this);
      syncColorOptions(this);
    };

    const origRebuildOptions = window.KTSelect.prototype._rebuildOptionsFromNative;
    if (origRebuildOptions) {
      window.KTSelect.prototype._rebuildOptionsFromNative = function () {
        origRebuildOptions.call(this);
        syncColorOptions(this);
      };
    }

    const origUpdateSelectedDisplay = window.KTSelect.prototype.updateSelectedOptionDisplay;
    if (origUpdateSelectedDisplay) {
      window.KTSelect.prototype.updateSelectedOptionDisplay = function () {
        syncColorOptions(this);
        origUpdateSelectedDisplay.call(this);

        const display = this.getValueDisplayElement?.() || this._displayElement;
        if (display) {
          const swatch = display.querySelector('.kt-color-swatch');
          if (swatch) {
            const selected = this.getSelectedOptions?.();
            const selectedVal = (selected && selected.length > 0) ? selected[0] : (this._element?.value ?? '');
            const cfg = this._config?.optionsConfig?.[selectedVal];
            if (cfg && cfg.color) {
              swatch.style.background = cfg.color;
              swatch.style.display = 'inline-block';
            } else {
              swatch.style.display = 'none';
            }
          }
        }
      };
    }
  }

  // KTSelect wrapper for Livewire properties, plain Blade forms, or Alpine repeater rows:
  // - Livewire: <div wire:ignore x-data="ktSelect('prop', { enableSearch: true })"><select x-ref="select" class="kt-select">…
  // - Plain Blade: <div x-data="ktSelect('prop')"><select x-ref="select" name="prop" class="kt-select">…
  // - Alpine object/row: <div x-data="ktSelect(item, 'kind')"><select x-ref="select" class="kt-select">…
  const ktSelectFactory = (targetOrProp, propOrConfig = {}, maybeConfig = {}) => {
    let target = null;
    let prop = null;
    let config = {};

    if (typeof targetOrProp === 'object' && targetOrProp !== null) {
      target = targetOrProp;
      prop = propOrConfig;
      config = maybeConfig || {};
    } else {
      prop = targetOrProp;
      config = propOrConfig || {};
    }

    const { live = false, ...selectConfig } = config;

    return {
      instance: null,
      init() {
        const select = this.$refs.select;
        if (!select) return;

        // Start from the bound value (an Alpine row's field, or the Livewire property) and mark its option `selected`:
        // KTSelect reads the selected attribute, not select.value, so a row built by Alpine (a quotation item's kind)
        // or an option without @selected would otherwise open with nothing chosen
        let bound = target && prop ? target[prop] : undefined;
        if (!target && prop) {
          try { bound = this.$wire?.get(prop); } catch (e) { /* a plain Blade form: no Livewire component around it */ }
        }
        if (bound !== undefined && bound !== null && [...select.options].some((option) => option.value === String(bound))) {
          select.value = String(bound);
        }
        // (a multiple select keeps every option its markup marked)
        if (!select.multiple) {
          [...select.options].forEach((option) => option.toggleAttribute('selected', option.value === select.value));
        }

        const emptyOpt = select.querySelector('option[value=""]');
        const explicitPlaceholder = selectConfig.placeholder
          || select.getAttribute('data-kt-select-placeholder')
          || select.getAttribute('data-placeholder');
        const placeholder = explicitPlaceholder
          || (emptyOpt && emptyOpt.textContent.trim() !== '' ? emptyOpt.textContent.trim() : (window.__ktSelectI18n?.placeholder || 'Select an option'));
        const searchPlaceholder = selectConfig.searchPlaceholder || window.__ktSelectI18n?.searchPlaceholder || 'Search...';
        const searchEmpty = selectConfig.searchEmpty || window.__ktSelectI18n?.searchEmpty || 'No results';

        if (window.KTSelect) {
          try {
            this.instance = KTSelect.getOrCreateInstance(select, {
              dropdownContainer: 'body',
              // This KTUI build never applies its `height` option, so cap the list here: long lists (100+ projects) scroll inside the dropdown.
              optionsClass: 'max-h-64 kt-scrollable-y-auto',
              placeholder,
              label: placeholder,
              searchPlaceholder,
              searchEmpty,
              ...selectConfig,
            });
          } catch (e) {
            console.error('KTSelect init error:', e);
          }
        }

        select.addEventListener('change', () => {
          if (target && prop) {
            target[prop] = select.value;
          } else if (prop && this.$wire) {
            this.$wire.$set(prop, select.value, live);
          }
        });

        if (target && prop) {
          this.$watch(() => target[prop], (val) => {
            this.sync(val);
          });
        } else if (prop && this.$wire) {
          this.$watch(`$wire.${prop}`, (val) => {
            this.sync(val);
          });
        }
      },
      sync(val) {
        const select = this.$refs.select;
        if (!select) return;
        const targetVal = val !== undefined
          ? (val ?? '')
          : (target && prop ? (target[prop] ?? '') : (this.$wire && prop ? (this.$wire[prop] ?? '') : select.value));

        const strVal = String(targetVal);
        if (select.value !== strVal) {
          select.value = strVal;
        }

        // Only update KTSelect display if KTSelect has completed its internal async setup
        if (this.instance && this.instance._options) {
          try {
            // KTSelect wants the <option> elements: a string is split into characters, matches nothing and clears the select
            this.instance.setSelectedOptions([...select.options].filter((option) => option.value === strVal));
            if (typeof this.instance.updateSelectedOptionDisplay === 'function') {
              this.instance.updateSelectedOptionDisplay();
            }
          } catch (e) {
            // Silently ignore if KTSelect is in intermediate state
          }
        }
      },
      destroy() {
        try {
          this.instance?.destroy();
        } catch (e) {
          // Ignore destroy errors
        }
      },
    };
  };

  // List picker (a promotion's products, gifts, branches): type a code or name, pick a suggestion (or Enter: an exact
  // code, e.g. a scanned SKU / barcode, adds at once), and the picks show as a list with a remove button; each is a
  // hidden `name[]` input. <div x-data="listPicker('gift_ids', @js($picked), productIndex)"> (index: [{id, name, codes}])
  window.Alpine.data('listPicker', (name, picked = [], index = []) => ({
    name,
    picked,
    q: '',
    active: 0,
    get matches() {
      const q = this.q.trim().toUpperCase();
      if (q === '') return [];
      const taken = new Set(this.picked.map((p) => p.id));
      return index
        .filter((p) => !taken.has(p.id) && (p.name.toUpperCase().includes(q) || p.codes.includes(q)))
        .slice(0, 10);
    },
    add(product) {
      if (!product || this.picked.some((p) => p.id === product.id)) return;
      this.picked.push({ id: product.id, name: product.name });
      this.q = '';
      this.active = 0;
    },
    remove(id) {
      this.picked = this.picked.filter((p) => p.id !== id);
    },
    enter() {
      const q = this.q.trim().toUpperCase();
      const exact = index.find((p) => p.codes.split(' ').includes(q));
      this.add(exact || this.matches[this.active]);
    },
    move(step) {
      const n = this.matches.length;
      if (n) this.active = (this.active + step + n) % n;
    },
  }));

  window.Alpine.data('ktSelect', ktSelectFactory);
  window.Alpine.data('ktSelectRow', ktSelectFactory);

  // Colour picker select: renders swatch square in front of each colour in the dropdown and display box
  const colorSelectTemplates = {
    enableSearch: true,
    optionTemplate: '<div class="flex items-center justify-between w-full"><div class="flex items-center gap-2"><span class="size-3.5 rounded-xs border border-border shrink-0 kt-color-swatch" style="background: {{color}}; display: {{hasColor}};"></span><span class="kt-select-option-text" data-kt-text-container="true">{{text}}</span></div><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5 ms-auto hidden text-primary kt-select-option-selected:block"><path d="M20 6 9 17l-5-5"/></svg></div>',
    displayTemplate: '<span class="flex items-center gap-2"><span class="size-3.5 rounded-xs border border-border shrink-0 kt-color-swatch" style="background: {{color}}; display: {{hasColor}};"></span><span>{{text}}</span></span>',
  };
  window.Alpine.data('ktColorSelect', (targetOrProp, maybeConfig = {}) => {
    const comp = ktSelectFactory(targetOrProp, { ...colorSelectTemplates, ...maybeConfig });
    const origInit = comp.init;
    comp.init = function () {
      origInit.call(this);
      const select = this.$refs.select;
      if (select) {
        select.addEventListener('dropdown.open', () => {
          if (this.instance) syncColorOptions(this.instance);
        });
      }
    };
    return comp;
  });

  // Global Lightbox for previewing images and PDFs
  // Area chart in the Metronic demo1 Earnings style (ApexCharts, loaded by the page with @push('scripts')): one series
  // in the theme's primary, dashed grid, smooth line, baht on hover. Colours are read from the CSS tokens at draw time.
  // <div x-data="earningsChart(labels, values, seriesName)"><div x-ref="chart"></div></div>
  window.Alpine.data('earningsChart', (labels, values, name) => ({
    chart: null,
    init() {
      if (!window.ApexCharts) return;
      const css = getComputedStyle(document.documentElement);
      const token = (prop, fallback) => css.getPropertyValue(prop).trim() || fallback;
      const primary = token('--primary', '#4a3426');
      const muted = token('--muted-foreground', '#78716c');
      const border = token('--border', '#e7e5e4');
      const baht = (v) => '฿' + Number(v).toLocaleString(undefined, { maximumFractionDigits: 0 });
      const short = (v) => (v >= 1e6 ? `฿${+(v / 1e6).toFixed(1)}M` : v >= 1e3 ? `฿${Math.round(v / 1e3)}K` : `฿${v}`);

      this.chart = new ApexCharts(this.$refs.chart, {
        series: [{ name, data: values }],
        chart: { type: 'area', height: 250, toolbar: { show: false }, fontFamily: 'inherit', zoom: { enabled: false } },
        dataLabels: { enabled: false },
        legend: { show: false },
        stroke: { curve: 'smooth', width: 3, colors: [primary] },
        colors: [primary],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0, stops: [0, 90, 100] } },
        markers: { size: 0, colors: '#fff', strokeColors: primary, strokeWidth: 3, hover: { size: 7 } },
        xaxis: {
          categories: labels,
          axisBorder: { show: false },
          axisTicks: { show: false },
          labels: { style: { colors: muted, fontSize: '12px' } },
          crosshairs: { position: 'front', stroke: { color: primary, width: 1, dashArray: 3 } },
          tooltip: { enabled: false },
        },
        yaxis: { min: 0, tickAmount: 5, labels: { formatter: short, style: { colors: muted, fontSize: '12px' } } },
        tooltip: { y: { formatter: baht } },
        grid: { borderColor: border, strokeDashArray: 5, xaxis: { lines: { show: false } }, yaxis: { lines: { show: true } } },
      });
      this.chart.render();
    },
    destroy() {
      this.chart?.destroy();
    },
  }));

  // <div x-data="paymentChart(labels, values, totalLabel)"><div x-ref="chart"></div></div> — a donut, browns from the theme
  window.Alpine.data('paymentChart', (labels, values, totalLabel) => ({
    chart: null,
    init() {
      if (!window.ApexCharts) return;
      const css = getComputedStyle(document.documentElement);
      const primary = css.getPropertyValue('--primary').trim() || '#4a3426';
      const muted = css.getPropertyValue('--muted-foreground').trim() || '#78716c';
      const baht = (v) => '฿' + Number(v).toLocaleString(undefined, { maximumFractionDigits: 0 });
      // the primary brown, then lighter browns (hex: ApexCharts shades them on hover)
      const colors = [primary, '#8a6a52', '#b39579', '#d4bfa6', '#ebe0d2'];

      this.chart = new ApexCharts(this.$refs.chart, {
        series: values,
        labels,
        chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
        colors,
        dataLabels: { enabled: false },
        stroke: { width: 2 },
        legend: { position: 'bottom', labels: { colors: muted } },
        tooltip: { y: { formatter: baht } },
        plotOptions: {
          pie: {
            donut: {
              size: '70%',
              labels: { show: true, total: { show: true, label: totalLabel, color: muted, formatter: (w) => baht(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) }, value: { formatter: baht } },
            },
          },
        },
      });
      this.chart.render();
    },
    destroy() {
      this.chart?.destroy();
    },
  }));

  window.Alpine.data('ktLightbox', () => ({
    isOpen: false,
    url: '',
    downloadUrl: '',
    name: '',
    size: '',
    isImage: false,
    isPdf: false,
    isLoading: false,
    zoom: 1,
    rotation: 0,

    init() {
      window.addEventListener('open-lightbox', (e) => {
        this.open(e.detail || {});
      });
      if (window.Livewire) {
        window.Livewire.on('open-lightbox', (data) => {
          const payload = Array.isArray(data) ? data[0] : data;
          this.open(payload || {});
        });
      }
    },

    open(data) {
      this.url = data.url || '';
      this.downloadUrl = data.downloadUrl || data.url || '';
      this.name = data.name || '';
      this.size = data.size || '';
      this.isImage = Boolean(data.isImage);
      this.isPdf = Boolean(data.isPdf);
      this.zoom = 1;
      this.rotation = 0;
      this.isLoading = true;
      this.isOpen = true;
      document.body.classList.add('overflow-hidden');
    },

    close() {
      this.isOpen = false;
      this.url = '';
      document.body.classList.remove('overflow-hidden');
    },

    zoomIn() {
      this.zoom = Math.min(+(this.zoom + 0.25).toFixed(2), 3);
    },

    zoomOut() {
      this.zoom = Math.max(+(this.zoom - 0.25).toFixed(2), 0.5);
    },

    resetZoom() {
      this.zoom = 1;
      this.rotation = 0;
    },

    rotate() {
      this.rotation = (this.rotation + 90) % 360;
    },

    onWheel(event) {
      if (!this.isImage) return;
      if (event.deltaY < 0) {
        this.zoomIn();
      } else {
        this.zoomOut();
      }
    },
  }));
};

if (window.Alpine) {
  initAlpineComponents();
} else {
  document.addEventListener('alpine:init', initAlpineComponents);
}

window.openLightbox = function (options) {
  window.dispatchEvent(new CustomEvent('open-lightbox', { detail: options }));
};

window.initTagify = function (input, options = {}) {
  if (!input || input.__tagify) return input?.__tagify;
  const opts = {
    whitelist: options.whitelist || [],
    enforceWhitelist: false,
    skipInvalid: true,
    trim: true,
    dropdown: {
      maxItems: 25,
      classname: 'kt-tagify-dropdown',
      enabled: 0,
      closeOnSelect: false,
    },
    originalInputValueFormat: (valuesArr) => valuesArr.map((item) => item.value).join(', '),
    ...options,
  };
  const tagify = new Tagify(input, opts);
  input.__tagify = tagify;
  const form = input.closest('form');
  if (form) {
    form.addEventListener('submit', () => {
      if (tagify?.DOM?.input) {
        const raw = tagify.DOM.input.textContent?.trim();
        if (raw) tagify.addTags(raw);
        input.value = tagify.getInputValue();
      }
    });
  }
  return tagify;
};
