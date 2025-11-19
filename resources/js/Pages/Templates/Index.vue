<template>
    <Head title="Templates" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Templates de Móveis</h1>
                    <p class="mt-2 text-gray-600">Escolha um template e personalize conforme suas necessidades</p>
                </div>

                <!-- Templates Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="template in templates"
                        :key="template.id"
                        class="bg-white rounded-lg shadow-sm hover:shadow-md transition cursor-pointer border border-gray-200"
                    >
                        <Link :href="route('projects.create', { template: template.id })" class="block p-6">
                            <!-- Template Icon/Image Placeholder -->
                            <div class="w-full h-48 bg-gradient-to-br from-primary-100 to-primary-200 rounded-lg mb-4 flex items-center justify-center">
                                <svg class="h-20 w-20 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z" />
                                </svg>
                            </div>

                            <!-- Template Info -->
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ template.name }}</h3>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ template.description }}</p>

                            <!-- Template Meta -->
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">{{ template.pieces_definition?.length || 0 }} peças</span>
                                <span class="text-primary-600 font-medium">Usar template →</span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="templates.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum template disponível</h3>
                    <p class="mt-1 text-sm text-gray-500">Entre em contato com o suporte para mais informações.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    templates: Array,
});
</script>
