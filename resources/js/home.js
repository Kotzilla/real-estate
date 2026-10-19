// The map search's map and listings (views/listings.blade.php, /properties): Alpine `propertyMap`. Filters narrow the listings; the map
// shows their pins (price labels); the list shows the ones in view when "follow the map" is on. The Mapbox map and its
// markers live outside Alpine's reactive state — a proxied map breaks Mapbox.
const FILTERS = { offer: 'all', types: [], areas: [], minPrice: '', maxPrice: '', beds: 0 };

document.addEventListener('alpine:init', () => {
  window.Alpine.data('propertyMap', (listings, config, labels) => {
    let map = null;
    const markers = new Map(); // listing id => { marker, el }

    return {
      listings,
      labels,
      ...structuredClone(FILTERS),
      // the landing page and the header link here with ?offer=sale|rent and ?area=Srithanu
      offer: new URLSearchParams(location.search).get('offer') || 'all',
      areas: new URLSearchParams(location.search).getAll('area'),
      sort: 'newest',
      followMap: true,
      bounds: null,
      active: null,
      filtersOpen: false,

      init() {
        if (!config.token || !window.mapboxgl) return;

        window.mapboxgl.accessToken = config.token;
        map = new window.mapboxgl.Map({
          container: this.$refs.map,
          style: 'mapbox://styles/mapbox/light-v11',
          center: config.center,
          zoom: config.zoom,
          scrollZoom: false,
        });
        map.addControl(new window.mapboxgl.NavigationControl({ showCompass: false }), 'top-right');

        this.listings.forEach((item) => {
          const el = document.createElement('button');
          el.type = 'button';
          el.className = 'map-pin';
          el.addEventListener('mouseenter', () => { this.active = item.id; });
          el.addEventListener('mouseleave', () => { this.active = null; });
          el.addEventListener('click', (event) => {
            event.stopPropagation();
            this.active = item.id;
            document.getElementById('card-' + item.id)?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
          });
          markers.set(item.id, { el, marker: new window.mapboxgl.Marker({ element: el }).setLngLat([item.lng, item.lat]).addTo(map) });
        });

        const track = () => { this.bounds = map.getBounds(); };
        map.on('load', track);
        map.on('moveend', track);

        // pins follow the filters, their labels the offer, their highlight the hovered card
        this.$watch('matching', () => this.paint());
        this.$watch('offer', () => this.paint());
        this.$watch('active', (id, old) => {
          markers.get(old)?.el.classList.remove('is-active');
          markers.get(id)?.el.classList.add('is-active');
        });
        this.paint();
      },

      // the listings that pass the filters, wherever they are
      get matching() {
        const min = Number(this.minPrice) || 0;
        const max = Number(this.maxPrice) || Infinity;

        return this.listings.filter((item) => {
          if (this.offer === 'sale' && !item.sale) return false;
          if (this.offer === 'rent' && !item.rent) return false;
          if (this.types.length && !this.types.includes(item.type)) return false;
          if (this.areas.length && !this.areas.includes(item.location)) return false;
          if (this.beds && item.bedrooms < this.beds) return false;
          const price = this.price(item);

          return price >= min && price <= max;
        });
      },

      // what the list shows: the matching ones in view (or all of them), sorted
      get visible() {
        const inView = this.followMap && this.bounds
          ? this.matching.filter((item) => this.bounds.contains([item.lng, item.lat]))
          : this.matching;
        const sorted = [...inView];
        if (this.sort === 'low') sorted.sort((a, b) => this.price(a) - this.price(b));
        if (this.sort === 'high') sorted.sort((a, b) => this.price(b) - this.price(a));

        return sorted;
      },

      // how many filters are set (the mobile button's badge, the Reset button)
      get filtered() {
        return ['offer', 'types', 'areas', 'minPrice', 'maxPrice', 'beds']
          .filter((key) => JSON.stringify(this[key]) !== JSON.stringify(FILTERS[key])).length;
      },

      reset() {
        Object.assign(this, structuredClone(FILTERS));
      },

      toggle(key, value) {
        this[key] = this[key].includes(value) ? this[key].filter((v) => v !== value) : [...this[key], value];
      },

      // the price that counts for filters, sorting and the pin: the rent when renting, else the sale price
      price(item) {
        return this.offer === 'rent' ? item.rent : (item.sale ?? item.rent);
      },

      money(amount) {
        return Number(amount || 0).toLocaleString();
      },

      // the price slider's top: the dearest listing for this offer, rounded up to a step (100k for sales, 1k for rents)
      get priceCeiling() {
        const top = Math.max(0, ...this.listings.map((item) => this.price(item) || 0));

        return Math.ceil(top / this.priceStep) * this.priceStep || this.priceStep;
      },

      get priceStep() {
        const top = Math.max(1, ...this.listings.map((item) => this.price(item) || 0));

        return 10 ** Math.max(0, Math.floor(Math.log10(top)) - 2);
      },

      // a price box keeps digits only and shows them with thousands separators
      typePrice(key, el) {
        const digits = el.value.replace(/\D/g, '');
        this[key] = digits ? Number(digits) : '';
        el.value = digits ? this.money(digits) : '';
      },

      // a slider handle can't pass the other one; the ends mean "no limit"
      slide(key, el) {
        let value = Number(el.value);
        if (key === 'minPrice') value = Math.min(value, (this.maxPrice || this.priceCeiling) - this.priceStep);
        if (key === 'maxPrice') value = Math.max(value, (this.minPrice || 0) + this.priceStep);
        el.value = value;
        this[key] = (key === 'minPrice' ? value <= 0 : value >= this.priceCeiling) ? '' : value;
      },

      // the slider's filled part, between the two handles
      sliderFill() {
        const pct = (value) => Math.min(100, (value / this.priceCeiling) * 100) + '%';

        return { '--lo': pct(this.minPrice || 0), '--hi': pct(this.maxPrice || this.priceCeiling) };
      },

      // a card price: 31.0M from a million up, else in full (45,000)
      compact(amount) {
        return amount >= 1e6 ? (amount / 1e6).toFixed(1) + 'M' : this.money(amount);
      },

      // "Villa in Srithanu"
      meta(item) {
        return labels.in.replace(':type', item.typeLabel).replace(':area', item.location || '');
      },

      // "3 bedrooms, 2 bathrooms, 220 m² living" — plain facts, the ones the listing has
      facts(item) {
        const count = (n, [one, many]) => (n === 1 ? one : many).replace(':count', n);

        return [
          item.bedrooms ? count(item.bedrooms, labels.bedrooms) : null,
          item.bathrooms ? count(item.bathrooms, labels.bathrooms) : null,
          item.area ? labels.area.replace(':m2', this.money(item.area)) : null,
          !item.area && item.land ? labels.land.replace(':m2', this.money(item.land)) : null,
        ].filter(Boolean).join(', ');
      },

      beds(item) {
        if (!item.bedrooms) return '';
        const [one, many] = labels.bedrooms;
        return (item.bedrooms === 1 ? one : many).replace(':count', item.bedrooms);
      },

      baths(item) {
        if (!item.bathrooms) return '';
        const [one, many] = labels.bathrooms;
        return (item.bathrooms === 1 ? one : many).replace(':count', item.bathrooms);
      },

      area(item) {
        if (item.area) return labels.area.replace(':m2', this.money(item.area));
        if (item.land) return labels.land.replace(':m2', this.money(item.land));
        return '';
      },

      // a short price for a pin: ฿14.9M, ฿45k/mo
      short(item) {
        const rent = this.offer === 'rent' || !item.sale;
        const amount = rent ? item.rent : item.sale;
        const text = amount >= 1e6 ? (amount / 1e6).toFixed(amount >= 1e7 ? 0 : 1).replace(/\.0$/, '') + 'M' : Math.round(amount / 1e3) + 'k';

        return '฿' + text + (rent ? labels.perMonth : '');
      },

      paint() {
        const shown = new Set(this.matching.map((item) => item.id));
        this.listings.forEach((item) => {
          const pin = markers.get(item.id);
          if (!pin) return;
          pin.el.textContent = this.short(item);
          pin.el.classList.toggle('is-rent', this.offer === 'rent' || !item.sale);
          pin.el.style.display = shown.has(item.id) ? '' : 'none';
        });
      },

      // a card was clicked: fly there and keep its pin lit
      focus(item) {
        this.active = item.id;
        map?.flyTo({ center: [item.lng, item.lat], zoom: Math.max(map.getZoom(), 14), speed: 1.4 });
      },
    };
  });
});
