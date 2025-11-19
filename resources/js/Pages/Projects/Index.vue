<template>
    <Head title="Meus Projetos" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Meus Projetos</h1>
                        <p class="mt-2 text-gray-600">Gerencie todos os seus projetos de móveis</p>
                    </div>
                    <Link
                        :href="route('projects.create')"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        + Novo Projeto
                    </Link>
                </div>

                <!-- Projects Grid -->
                <div v-if="projects.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="project in projects.data"
                        :key="project.id"
                        class="bg-white rounded-lg shadow-sm hover:shadow-md transition border border-gray-200 overflow-hidden"
                    >
                        <Link :href="route('projects.show', project.id)" class="block">
                            <!-- Project Visual -->
                            <div class="h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                <svg class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>

                            <!-- Project Info -->
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 flex-1 mr-2">{{ project.name }}</h3>
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full flex-shrink-0',
                                            project.status === 'ready' ? 'bg-green-100 text-green-800' :
                                            project.status === 'processing' ? 'bg-yellow-100 text-yellow-800' :
                                            project.status === 'failed' ? 'bg-red-100 text-red-800' :
                                            'bg-gray-100 text-gray-800'
                                        ]"
                                    >
                                        {{ getStatusLabel(project.status) }}
                                    </span>
                                </div>

                                <div class="space-y-1 text-sm text-gray-600 mb-4">
                                    <p>{{ project.template?.name }}</p>
                                    <p>{{ project.material?.name }}</p>
                                </div>

                                <div v-if="project.estimated_price" class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-sm text-gray-500">Preço estimado</p>
                                    <p class="text-xl font-bold text-primary-600">
                                        {{ formatCurrency(project.estimated_price) }}
                                    </p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12 bg-white rounded-lg shadow-sm">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum projeto criado</h3>
                    <p class="mt-1 text-sm text-gray-500">Comece criando seu primeiro projeto de móvel.</p>
                    <div class="mt-6">
                        <Link
                            :href="route('projects.create')"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
                        >
                            Criar Primeiro Projeto
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="projects.data.length > 0 && (projects.prev_page_url || projects.next_page_url)" class="mt-8 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <Link
                            v-if="projects.prev_page_url"
                            :href="projects.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="projects.next_page_url"
                            :href="projects.next_page_url"
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
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    projects: Object,
});

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pendente',
        'processing': 'Processando',
        'ready': 'Pronto',
        'failed': 'Falhou',
    };
    return labels[status] || status;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};
</script>
