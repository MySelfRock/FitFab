<template>
    <Head title="Verificar Profissionais" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Verificar Profissionais</h1>
                        <p class="mt-2 text-gray-600">{{ professionals.total }} profissionais no total</p>
                    </div>
                    <Link :href="route('admin.dashboard')" class="text-sm text-gray-600 hover:text-gray-900">
                        ← Voltar ao Dashboard
                    </Link>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex gap-4">
                        <select
                            v-model="filterStatus"
                            @change="filterProfessionals"
                            class="border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                        >
                            <option value="">Todos os status</option>
                            <option value="verified">Verificados</option>
                            <option value="pending">Aguardando Verificação</option>
                        </select>
                        <select
                            v-model="filterActive"
                            @change="filterProfessionals"
                            class="border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                        >
                            <option value="">Ativos e Inativos</option>
                            <option value="true">Apenas Ativos</option>
                            <option value="false">Apenas Inativos</option>
                        </select>
                    </div>
                </div>

                <!-- Professionals Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="professional in professionals.data"
                        :key="professional.id"
                        class="bg-white rounded-lg shadow-sm p-6"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ professional.business_name }}</h3>
                                <p class="text-sm text-gray-500">{{ professional.user?.name }}</p>
                                <p class="text-xs text-gray-400">{{ professional.specialty }}</p>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span
                                    v-if="professional.verified"
                                    class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800"
                                >
                                    Verificado
                                </span>
                                <span
                                    v-else
                                    class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800"
                                >
                                    Pendente
                                </span>
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        professional.active ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                                    ]"
                                >
                                    {{ professional.active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-4 text-sm text-gray-600 space-y-1">
                            <div class="flex items-center justify-between">
                                <span>Ofertas:</span>
                                <span class="font-medium">{{ professional.offers_count || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Pedidos:</span>
                                <span class="font-medium">{{ professional.orders_count || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Avaliação:</span>
                                <span class="font-medium">{{ professional.rating?.toFixed(1) || '0.0' }} ★</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Link
                                :href="route('admin.professionals.show', professional.id)"
                                class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                            >
                                Ver Detalhes
                            </Link>
                            <button
                                v-if="!professional.verified"
                                @click="verify(professional.id)"
                                class="w-full px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700"
                            >
                                Verificar
                            </button>
                            <button
                                v-else
                                @click="unverify(professional.id)"
                                class="w-full px-4 py-2 bg-yellow-600 text-white rounded-md text-sm hover:bg-yellow-700"
                            >
                                Remover Verificação
                            </button>
                            <button
                                @click="toggleActive(professional.id)"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                            >
                                {{ professional.active ? 'Desativar' : 'Ativar' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="professionals.links && professionals.links.length > 3" class="mt-6 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <Link
                            v-for="(link, index) in professionals.links"
                            :key="index"
                            :href="link.url"
                            :class="[
                                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                link.active ? 'z-10 bg-primary-600 border-primary-600 text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                index === 0 ? 'rounded-l-md' : '',
                                index === professionals.links.length - 1 ? 'rounded-r-md' : ''
                            ]"
                            v-html="link.label"
                        />
                    </nav>
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
    professionals: Object,
    filters: Object,
});

const filterStatus = ref(props.filters?.status || '');
const filterActive = ref(props.filters?.active || '');

const filterProfessionals = () => {
    router.get(route('admin.professionals.index'), {
        status: filterStatus.value,
        active: filterActive.value,
    }, {
        preserveState: true,
    });
};

const verify = (id) => {
    if (confirm('Deseja verificar este profissional?')) {
        router.post(route('admin.professionals.verify', id));
    }
};

const unverify = (id) => {
    if (confirm('Deseja remover a verificação deste profissional?')) {
        router.post(route('admin.professionals.unverify', id));
    }
};

const toggleActive = (id) => {
    router.post(route('admin.professionals.toggle-active', id));
};
</script>
