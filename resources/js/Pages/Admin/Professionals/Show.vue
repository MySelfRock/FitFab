<template>
    <Head :title="`Profissional: ${professional.business_name}`" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <Link :href="route('admin.professionals.index')" class="text-sm text-gray-600 hover:text-gray-900 mb-4 inline-block">
                        ← Voltar para Profissionais
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-900">{{ professional.business_name }}</h1>
                    <p class="mt-2 text-gray-600">{{ professional.specialty }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Info -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-semibold mb-4">Informações</h2>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Usuário</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ professional.user?.name }} ({{ professional.user?.email }})</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Descrição</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ professional.description || 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Raio de Atendimento</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ professional.service_radius_km }} km</dd>
                                </div>
                                <div v-if="professional.portfolio_url">
                                    <dt class="text-sm font-medium text-gray-500">Portfólio</dt>
                                    <dd class="mt-1 text-sm">
                                        <a :href="professional.portfolio_url" target="_blank" class="text-primary-600 hover:text-primary-900">
                                            {{ professional.portfolio_url }}
                                        </a>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Stats -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-semibold mb-4">Estatísticas</h2>
                            <dl class="grid grid-cols-4 gap-4">
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <dt class="text-sm text-gray-500">Ofertas</dt>
                                    <dd class="mt-1 text-2xl font-bold text-gray-900">{{ stats.total_offers }}</dd>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <dt class="text-sm text-gray-500">Aceitas</dt>
                                    <dd class="mt-1 text-2xl font-bold text-green-600">{{ stats.accepted_offers }}</dd>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <dt class="text-sm text-gray-500">Taxa Aceitação</dt>
                                    <dd class="mt-1 text-2xl font-bold text-gray-900">{{ stats.acceptance_rate }}%</dd>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <dt class="text-sm text-gray-500">Receita Total</dt>
                                    <dd class="mt-1 text-xl font-bold text-gray-900">{{ formatCurrency(stats.total_earned) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Admin Notes -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-semibold mb-4">Observações Administrativas</h2>
                            <form @submit.prevent="updateNotes">
                                <textarea
                                    v-model="notesForm.admin_notes"
                                    rows="4"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Adicione observações sobre este profissional..."
                                />
                                <button type="submit" class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                                    Salvar Observações
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-semibold mb-4">Status</h3>
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">Verificado:</span>
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            professional.verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                        ]"
                                    >
                                        {{ professional.verified ? 'Sim' : 'Não' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">Ativo:</span>
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            professional.active ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                                        ]"
                                    >
                                        {{ professional.active ? 'Sim' : 'Não' }}
                                    </span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <button
                                    v-if="!professional.verified"
                                    @click="verify"
                                    class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                >
                                    Verificar Profissional
                                </button>
                                <button
                                    v-else
                                    @click="unverify"
                                    class="w-full px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700"
                                >
                                    Remover Verificação
                                </button>
                                <button
                                    @click="toggleActive"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                                >
                                    {{ professional.active ? 'Desativar' : 'Ativar' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    professional: Object,
    stats: Object,
});

const notesForm = ref({
    admin_notes: props.professional.admin_notes || '',
});

const updateNotes = () => {
    router.post(route('admin.professionals.update-notes', props.professional.id), notesForm.value);
};

const verify = () => {
    if (confirm('Deseja verificar este profissional?')) {
        router.post(route('admin.professionals.verify', props.professional.id));
    }
};

const unverify = () => {
    if (confirm('Deseja remover a verificação?')) {
        router.post(route('admin.professionals.unverify', props.professional.id));
    }
};

const toggleActive = () => {
    router.post(route('admin.professionals.toggle-active', props.professional.id));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value || 0);
};
</script>
