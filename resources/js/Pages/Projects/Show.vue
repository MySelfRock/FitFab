<template>
    <Head :title="project.name" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-4">
                                <Link :href="route('projects.index')" class="text-gray-400 hover:text-gray-600">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </Link>
                                <h1 class="text-3xl font-bold text-gray-900">{{ project.name }}</h1>
                                <span
                                    :class="[
                                        'px-3 py-1 text-sm font-medium rounded-full',
                                        project.status === 'ready' ? 'bg-green-100 text-green-800' :
                                        project.status === 'processing' ? 'bg-yellow-100 text-yellow-800' :
                                        project.status === 'failed' ? 'bg-red-100 text-red-800' :
                                        'bg-gray-100 text-gray-800'
                                    ]"
                                >
                                    {{ getStatusLabel(project.status) }}
                                </span>
                            </div>
                            <p class="mt-2 text-gray-600">
                                {{ project.template?.name }} • {{ project.material?.name }}
                            </p>
                        </div>
                        <div v-if="project.status === 'ready'" class="flex items-center space-x-3">
                            <!-- Download Dropdown -->
                            <div class="relative">
                                <button
                                    @click="showDownloadMenu = !showDownloadMenu"
                                    class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 transition"
                                >
                                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download
                                    <svg class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div
                                    v-show="showDownloadMenu"
                                    @click="showDownloadMenu = false"
                                    class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10"
                                >
                                    <div class="py-1">
                                        <a
                                            :href="route('projects.download', { project: project.id, format: 'csv' })"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        >
                                            Download CSV
                                        </a>
                                        <a
                                            :href="route('projects.download', { project: project.id, format: 'svg' })"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        >
                                            Download SVG
                                        </a>
                                        <a
                                            :href="route('projects.download', { project: project.id, format: 'pdf' })"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        >
                                            Download PDF
                                        </a>
                                        <a
                                            :href="route('projects.download', { project: project.id, format: 'dxf' })"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        >
                                            Download DXF
                                        </a>
                                        <hr class="my-1">
                                        <a
                                            :href="route('projects.downloadAll', project.id)"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 font-semibold"
                                        >
                                            Download Tudo (ZIP)
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Regenerate Button -->
                            <button
                                @click="regenerateProject"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition"
                            >
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Regenerar
                            </button>
                        </div>

                        <!-- Failed State Actions -->
                        <div v-if="project.status === 'failed'" class="flex items-center space-x-3">
                            <button
                                @click="regenerateProject"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 transition"
                            >
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Tentar Novamente
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Processing State -->
                <div v-if="project.status === 'processing'" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                    <div class="flex items-center">
                        <svg class="animate-spin h-5 w-5 text-yellow-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-yellow-800">Processando projeto...</h3>
                            <p class="mt-1 text-sm text-yellow-700">Estamos otimizando o corte das peças. Isso pode levar alguns minutos.</p>
                        </div>
                    </div>
                </div>

                <!-- Failed State -->
                <div v-if="project.status === 'failed'" class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-red-800">Erro ao processar projeto</h3>
                            <p class="mt-1 text-sm text-red-700">Ocorreu um erro ao processar este projeto. Clique em "Tentar Novamente" para reprocessar.</p>
                        </div>
                    </div>
                </div>

                <!-- Project Summary -->
                <div v-if="project.status === 'ready'" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-primary-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Peças</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ project.pieces?.length || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Chapas</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ project.sheets?.length || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Aproveitamento</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ project.metrics?.average_utilization?.toFixed(1) || 0 }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Preço Est.</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ formatCurrency(project.estimated_price || 0) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="project.status === 'ready'" class="space-y-6">
                    <!-- Pieces Table -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Lista de Peças</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Largura</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Altura</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borda</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="piece in project.pieces" :key="piece.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ piece.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ piece.width_mm }} mm</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ piece.height_mm }} mm</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ piece.qty }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ piece.edge_banding ? JSON.parse(piece.edge_banding).join(', ') : '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sheets Layout -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Plano de Corte</h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div v-for="sheet in project.sheets" :key="sheet.id" class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-sm font-medium text-gray-900">Chapa #{{ sheet.sheet_number }}</h3>
                                    <div class="text-sm text-gray-500">
                                        Aproveitamento: <span class="font-semibold text-green-600">{{ sheet.utilization?.toFixed(1) }}%</span>
                                    </div>
                                </div>
                                <div v-if="sheet.svg_content" class="bg-gray-50 rounded p-4 overflow-auto" v-html="sheet.svg_content"></div>
                                <div v-else class="bg-gray-50 rounded p-12 text-center text-gray-400">
                                    <p>Layout não disponível</p>
                                </div>
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
    project: Object,
});

const showDownloadMenu = ref(false);

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

const regenerateProject = () => {
    if (confirm('Deseja realmente regenerar este projeto? Isso irá reprocessar todos os cortes e arquivos.')) {
        router.post(route('projects.regenerate', props.project.id), {}, {
            preserveScroll: true,
        });
    }
};
</script>
