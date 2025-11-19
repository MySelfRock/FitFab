<template>
    <Head title="Pedidos" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ isProfessional ? 'Meus Pedidos (Profissional)' : 'Meus Pedidos' }}
                    </h1>
                    <p class="mt-2 text-gray-600">Gerencie seus pedidos</p>
                </div>

                <div v-if="orders.data.length > 0" class="space-y-4">
                    <div
                        v-for="order in orders.data"
                        :key="order.id"
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">Pedido #{{ order.order_number }}</h3>
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            order.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                            order.status === 'paid' ? 'bg-blue-100 text-blue-800' :
                                            order.status === 'completed' ? 'bg-green-100 text-green-800' :
                                            'bg-gray-100 text-gray-800'
                                        ]"
                                    >
                                        {{ getStatusLabel(order.status) }}
                                    </span>
                                </div>

                                <p class="text-sm text-gray-600 mb-3">
                                    Projeto: {{ order.project.name }} • {{ order.project.template?.name }}
                                </p>

                                <div class="flex items-center space-x-4 text-sm">
                                    <div class="flex items-center text-primary-600 font-semibold">
                                        {{ formatCurrency(order.amount) }}
                                    </div>
                                    <div class="text-gray-500">
                                        {{ formatDate(order.created_at) }}
                                    </div>
                                </div>
                            </div>

                            <Link
                                :href="route('orders.show', order.id)"
                                class="ml-4 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Ver Detalhes
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 bg-white rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-900">Nenhum pedido</h3>
                    <p class="mt-1 text-sm text-gray-500">Você ainda não tem pedidos.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    orders: Object,
    isProfessional: Boolean,
});

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pendente',
        'paid': 'Pago',
        'in_progress': 'Em Progresso',
        'completed': 'Concluído',
        'cancelled': 'Cancelado',
    };
    return labels[status] || status;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR');
};
</script>
