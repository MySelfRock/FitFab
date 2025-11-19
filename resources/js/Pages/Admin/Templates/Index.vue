<template>
    <Head title="Gerenciar Templates" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Gerenciar Templates</h1>
                        <p class="mt-2 text-gray-600">{{ templates.total }} templates no total</p>
                    </div>
                    <div class="flex gap-4">
                        <Link :href="route('admin.templates.create')" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                            Novo Template
                        </Link>
                        <Link :href="route('admin.dashboard')" class="text-sm text-gray-600 hover:text-gray-900 self-center">
                            ← Dashboard
                        </Link>
                    </div>
                </div>

                <!-- Templates Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dimensões</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Projetos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="template in templates.data" :key="template.id">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-sm text-gray-900">{{ template.name }}</div>
                                    <div class="text-xs text-gray-500 line-clamp-1">{{ template.description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ template.category }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    {{ template.default_width }} × {{ template.default_height }} × {{ template.default_depth }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ template.projects_count || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            template.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                        ]"
                                    >
                                        {{ template.active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                    <Link
                                        :href="route('admin.templates.edit', template.id)"
                                        class="text-primary-600 hover:text-primary-900"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        @click="toggleActive(template.id)"
                                        class="text-gray-600 hover:text-gray-900"
                                    >
                                        {{ template.active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                    <button
                                        @click="deleteTemplate(template.id)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Excluir
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    templates: Object,
});

const toggleActive = (id) => {
    router.post(route('admin.templates.toggle-active', id));
};

const deleteTemplate = (id) => {
    if (confirm('Deseja realmente excluir este template?')) {
        router.delete(route('admin.templates.destroy', id));
    }
};
</script>
