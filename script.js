'use strict';

const COUNTER_DURATION_MS = 1400;
const WHATSAPP_NUMBER = '6283899781107';
const PHONE_PATTERN = /^(\+62|62|0)8[0-9]{7,12}$/;

function pilihPaket(namaGunung) {
    const select = document.getElementById('paketSelect');
    if (select) select.value = namaGunung;
}

function kirimWhatsApp(event) {
    event.preventDefault();

    const nama = document.getElementById('nama').value.trim();
    const paket = document.getElementById('paketSelect').value;
    const jumlah = parseInt(document.getElementById('jumlah').value, 10);
    const tanggal = document.getElementById('tanggal').value;
    const today = new Date().toISOString().split('T')[0];

    if (!Number.isInteger(jumlah) || jumlah < 1) {
        alert('Jumlah peserta minimal 1 orang.');
        return false;
    }
    if (tanggal < today) {
        alert('Tanggal pendakian tidak boleh di masa lalu.');
        return false;
    }

    const tanggalIndo = new Date(tanggal + 'T00:00:00').toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    });

    const pesan = [
        'Halo FUNTrip, saya ingin memesan paket pendakian.',
        '',
        'Nama: ' + nama,
        'Paket: ' + paket,
        'Jumlah peserta: ' + jumlah + ' orang',
        'Tanggal pendakian: ' + tanggalIndo
    ].join('\n');

    const url = 'https://wa.me/' + WHATSAPP_NUMBER + '?text=' + encodeURIComponent(pesan);
    window.open(url, '_blank', 'noopener,noreferrer');
    return false;
}

function animateCounter(el) {
    if (el.dataset.done) return;
    el.dataset.done = '1';

    const target = parseFloat(el.dataset.count);
    const decimals = parseInt(el.dataset.decimals || '0', 10);
    const suffix = el.dataset.suffix || '';
    const startTime = performance.now();

    function tick(now) {
        const progress = Math.min((now - startTime) / COUNTER_DURATION_MS, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = (target * eased).toFixed(decimals) + suffix;
        if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

document.addEventListener('DOMContentLoaded', () => {
    const tanggal = document.getElementById('tanggal');
    if (tanggal) tanggal.min = new Date().toISOString().split('T')[0];

    const counters = document.querySelectorAll('[data-count]');
    if (!('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.4 });

    counters.forEach((el) => observer.observe(el));
});