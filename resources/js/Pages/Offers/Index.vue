<template>
    <Head title="Propostas" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ isProfessional ? 'Minhas Propostas Enviadas' : 'Propostas Recebidas' }}
                        </h1>
                        <p class="mt-2 text-gray-600">
                            {{ isProfessional ? 'Gerencie as propostas que você enviou para projetos' : 'Analise as propostas recebidas nos seus projetos' }}
                        </p>
                    </div>
                </div>

                <!-- Offers List -->
                <div v-if="offers.data.length > 0" class="space-y-4">
                    <div
                        v-for="offer in offers.data"
                        :key="offer.id"
                        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <!-- Offer Info -->
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ offer.project.name }}
                                        </h3>
                                        <span
                                            :class="[
                                                'px-2 py-1 text-xs font-medium rounded-full',
                                                offer.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                offer.status === 'accepted' ? 'bg-green-100 text-green-800' :
                                                'bg-gray-100 text-gray-800'
                                            ]"
                                        >
                                            {{ getStatusLabel(offer.status) }}
                                        </span>
                                    </div>

                                    <!-- Professional/User Info -->
                                    <div class="flex items-center text-sm text-gray-600 mb-3">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>
                                            {{ isProfessional ? `Cliente: ${offer.project.user?.name}` : `Profissional: ${offer.professional?.user?.name}` }}
                                        </span>
                                    </div>

                                    <!-- Project Details -->
                                    <p class="text-sm text-gray-600 mb-2">
                                        {{ offer.project.template?.name }} • {{ offer.project.material?.name }}
                                    </p>

                                    <!-- Offer Message Preview -->
                                    <p class="text-sm text-gray-700 line-clamp-2 mb-3">
                                        {{ offer.message }}
                                    </p>

                                    <!-- Offer Details -->
                                    <div class="flex items-center space-x-4 text-sm">
                                        <div class="flex items-center text-primary-600 font-semibold">
                                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ formatCurrency(offer.price) }}
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ offer.delivery_days }} dias
                                        </div>
                                        <div class="flex items-center text-gray-500 text-xs">
                                            {{ formatDate(offer.created_at) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="ml-4 flex flex-col space-y-2">
                                    <Link
                                        :href="route('offers.show', offer.id)"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                                    >
                                        Ver Detalhes
                                    </Link>

                                    <!-- User Actions (accept/reject) -->
                                    <template v-if="!isProfessional && offer.status === 'pending'">
                                        <button
                                            @click="acceptOffer(offer.id)"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 transition"
                                        >
                                            Aceitar
                                        </button>
                                        <button
                                            @click="rejectOffer(offer.id)"
                                            class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 hover:bg-red-50 transition"
                                        >
                                            Rejeitar
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12 bg-white rounded-lg shadow-sm">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma proposta</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ isProfessional ? 'Você ainda não enviou nenhuma proposta.' : 'Você ainda não recebeu propostas para seus projetos.' }}
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="offers.data.length > 0 && (offers.prev_page_url || offers.next_page_url)" class="mt-8 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <Link
                            v-if="offers.prev_page_url"
                            :href="offers.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="offers.next_page_url"
                            :href="offers.next_page_url"
                            class="relative inline-flex items-center px-4 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Próximo
                        </Link>
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    offers: Object,
    isProfessional: Boolean,
});

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pendente',
        'accepted': 'Aceita',
        'rejected': 'Rejeitada',
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
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const acceptOffer = (offerId) => {
    if (confirm('Deseja realmente aceitar esta proposta? As outras propostas serão automaticamente rejeitadas.')) {
        router.post(route('offers.accept', offerId));
    }
};

const rejectOffer = (offerId) => {
    if (confirm('Deseja realmente rejeitar esta proposta?')) {
        router.post(route('offers.reject', offerId));
    }
};
</script>
