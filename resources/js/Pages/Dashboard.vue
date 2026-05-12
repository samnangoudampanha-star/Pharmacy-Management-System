<template>
    <div>
        <Head :title="$t('dashboard.title')" />

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="text-muted small">{{ $t('dashboard.total_branches') }}</div>
                        <h3 class="mb-0">{{ stats.total_branches }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-start border-success border-4">
                    <div class="card-body">
                        <div class="text-muted small">{{ $t('dashboard.total_products') }}</div>
                        <h3 class="mb-0">{{ stats.total_products }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-start border-info border-4">
                    <div class="card-body">
                        <div class="text-muted small">{{ $t('dashboard.today_sales') }}</div>
                        <h3 class="mb-0">{{ format(stats.today_sales) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-start border-warning border-4">
                    <div class="card-body">
                        <div class="text-muted small">{{ $t('dashboard.today_purchases') }}</div>
                        <h3 class="mb-0">{{ format(stats.today_purchases) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <strong>{{ $t('dashboard.recent_sales') }}</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ $t('fields.invoice_number') }}</th>
                                    <th>{{ $t('fields.customer') }}</th>
                                    <th>{{ $t('fields.branch') }}</th>
                                    <th class="text-end">{{ $t('fields.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="s in recent_sales" :key="s.id">
                                    <td>{{ s.invoice_number }}</td>
                                    <td>{{ s.customer?.name || '—' }}</td>
                                    <td>{{ s.branch?.name }}</td>
                                    <td class="text-end">{{ format(s.total) }}</td>
                                </tr>
                                <tr v-if="!recent_sales.length">
                                    <td colspan="4" class="text-center text-muted py-3">
                                        {{ $t('common.no_data') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <strong>{{ $t('dashboard.recent_purchases') }}</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ $t('fields.reference_number') }}</th>
                                    <th>{{ $t('fields.supplier') }}</th>
                                    <th>{{ $t('fields.branch') }}</th>
                                    <th class="text-end">{{ $t('fields.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in recent_purchases" :key="p.id">
                                    <td>{{ p.reference_number }}</td>
                                    <td>{{ p.supplier?.name }}</td>
                                    <td>{{ p.branch?.name }}</td>
                                    <td class="text-end">{{ format(p.total) }}</td>
                                </tr>
                                <tr v-if="!recent_purchases.length">
                                    <td colspan="4" class="text-center text-muted py-3">
                                        {{ $t('common.no_data') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    stats: { type: Object, required: true },
    recent_sales: { type: Array, default: () => [] },
    recent_purchases: { type: Array, default: () => [] },
    sales_by_branch: { type: Array, default: () => [] },
});

function format(n) {
    const num = Number(n || 0);
    return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
