import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Alpine = Alpine;
window.Chart = Chart;
window.Swal = Swal;

window.KasirkuSwal = {
    toast(message, icon = 'success') {
        if (!message) return;

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon,
            title: message,
            showConfirmButton: false,
            timer: 3200,
            timerProgressBar: true,
        });
    },

    confirm(options = {}) {
        return Swal.fire({
            title: options.title || 'Lanjutkan?',
            text: options.text || 'Aksi ini akan diproses sekarang.',
            icon: options.icon || 'question',
            showCancelButton: true,
            confirmButtonText: options.confirmButtonText || 'Ya, lanjutkan',
            cancelButtonText: options.cancelButtonText || 'Batal',
            reverseButtons: true,
            focusCancel: true,
            customClass: {
                confirmButton: 'swal2-kasirku-confirm',
                cancelButton: 'swal2-kasirku-cancel',
            },
        });
    },
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-swal-toast]').forEach((element) => {
        window.KasirkuSwal.toast(element.dataset.message, element.dataset.type || 'success');
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-confirm]');

        if (!form || form.dataset.confirmed === 'true') return;

        event.preventDefault();

        const result = await window.KasirkuSwal.confirm({
            title: form.dataset.confirmTitle,
            text: form.dataset.confirm,
            icon: form.dataset.confirmIcon || 'warning',
            confirmButtonText: form.dataset.confirmButton || 'Ya, proses',
            cancelButtonText: form.dataset.cancelButton || 'Batal',
        });

        if (!result.isConfirmed) return;

        form.dataset.confirmed = 'true';
        form.submit();
    });
});

Alpine.start();

window.dispatchEvent(new Event('kasirku:charts-ready'));
