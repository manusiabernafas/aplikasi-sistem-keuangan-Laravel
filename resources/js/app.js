import * as bootstrap from 'bootstrap';
import Swal from 'sweetalert2';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

window.bootstrap = bootstrap;
window.Swal = Swal;
window.Chart = Chart;

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('.app-sidebar');

    if (toggle && sidebar) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('show'));

        document.addEventListener('click', (event) => {
            if (window.innerWidth >= 992) return;
            if (!sidebar.classList.contains('show')) return;
            if (sidebar.contains(event.target) || toggle.contains(event.target)) return;
            sidebar.classList.remove('show');
        });
    }

    const flash = document.querySelector('[data-flash-success]');
    if (flash) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: flash.dataset.flashSuccess,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    }

    const flashError = document.querySelector('[data-flash-error]');
    if (flashError) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: flashError.dataset.flashError,
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
        });
    }

    document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: form.dataset.confirmDelete || 'Hapus data ini?',
                text: 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#DC2626',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    initQuickTransactionModal();
    initDashboardCharts();
});

function initDashboardCharts() {
    const dataEl = document.getElementById('dashboardChartData');
    if (!dataEl) return;

    const { trend, expenseByCategory } = JSON.parse(dataEl.textContent);

    const trendCanvas = document.getElementById('incomeExpenseChart');
    if (trendCanvas) {
        new Chart(trendCanvas, {
            type: 'bar',
            data: {
                labels: trend.labels,
                datasets: [
                    { label: 'Pemasukan', data: trend.income, backgroundColor: '#16A34A' },
                    { label: 'Pengeluaran', data: trend.expense, backgroundColor: '#DC2626' },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { ticks: { callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID') } } },
            },
        });
    }

    const categoryCanvas = document.getElementById('expenseCategoryChart');
    if (categoryCanvas && expenseByCategory.labels.length) {
        new Chart(categoryCanvas, {
            type: 'doughnut',
            data: {
                labels: expenseByCategory.labels,
                datasets: [{
                    data: expenseByCategory.values,
                    backgroundColor: ['#4338CA', '#DC2626', '#D97706', '#0891B2', '#16A34A', '#64748B', '#DB2777', '#7C3AED'],
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }
}

function initQuickTransactionModal() {
    const modalEl = document.getElementById('quick-transaction-modal');
    if (!modalEl) return;

    const typeInput = document.getElementById('quick_transaction_type');
    const categorySelect = document.getElementById('quick_category_id');
    const errorBox = document.getElementById('quickTransactionError');
    const submitBtn = document.getElementById('quickTransactionSubmit');
    const form = document.getElementById('quickTransactionForm');

    modalEl.querySelectorAll('[data-quick-tab]').forEach((tabBtn) => {
        tabBtn.addEventListener('click', () => {
            modalEl.querySelectorAll('[data-quick-tab]').forEach((btn) => btn.classList.remove('active'));
            tabBtn.classList.add('active');

            const type = tabBtn.dataset.quickTab;
            typeInput.value = type;
            submitBtn.classList.toggle('btn-primary', type === 'INCOME');
            submitBtn.classList.toggle('btn-danger', type === 'EXPENSE');

            const options = JSON.parse(categorySelect.dataset[type === 'INCOME' ? 'incomeOptions' : 'expenseOptions'] || '[]');
            categorySelect.innerHTML = options.map((o) => `<option value="${o.id}">${o.name}</option>`).join('');
        });
    });

    submitBtn.addEventListener('click', () => {
        errorBox.classList.add('d-none');

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        fetch(form.dataset.action || '/transactions/quick', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(payload),
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Transaksi gagal disimpan.');
                }
                return data;
            })
            .then((data) => {
                bootstrap.Modal.getInstance(modalEl)?.hide();
                form.reset();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                }).then(() => window.location.reload());
            })
            .catch((error) => {
                errorBox.textContent = error.message;
                errorBox.classList.remove('d-none');
            });
    });
}
