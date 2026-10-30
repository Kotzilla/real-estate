// Textarea that grows with its content; the ring fills up to its maxlength, turns red and shows the characters left
// for the last 50 (<x-textarea>, office's design). Its own module so the public pages can load it without app.js.
// <div x-data="limitedTextarea(500)"> … <svg ring, uses ratio/remaining/color> … <textarea x-ref="ta" @input="sync" maxlength="500">
const register = () => window.Alpine.data('limitedTextarea', (max = 500) => ({
  max,
  len: 0,
  init() {
    this.sync();
    this.$nextTick(() => this.sync());
  },
  sync() {
    const ta = this.$refs.ta;
    if (!ta) return;
    ta.style.height = 'auto';
    ta.style.height = `${ta.scrollHeight + 2}px`;
    this.len = (ta.value || '').length;
  },
  get remaining() {
    return this.max - this.len;
  },
  get ratio() {
    return Math.min(this.len / this.max, 1);
  },
  get near() {
    return this.remaining <= 50;
  },
  get color() {
    return this.near ? 'var(--destructive)' : 'var(--primary)';
  },
}));

if (window.Alpine) {
  register();
} else {
  document.addEventListener('alpine:init', register);
}
