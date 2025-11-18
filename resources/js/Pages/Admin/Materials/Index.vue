<template>
    <Head title="Gerenciar Materiais" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Gerenciar Materiais</h1>
                        <p class="mt-2 text-gray-600">{{ materials.total }} materiais no total</p>
                    </div>
                    <div class="flex gap-4">
                        <Link :href="route('admin.materials.create')" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                            Novo Material
                        </Link>
                        <Link :href="route('admin.dashboard')" class="text-sm text-gray-600 hover:text-gray-900 self-center">
                            ← Dashboard
                        </Link>
                    </div>
                </div>

                <!-- Materials Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dimensões</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preço</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Projetos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="material in materials.data" :key="material.id">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-sm text-gray-900">{{ material.name }}</div>
                                    <div class="text-xs text-gray-500">{{ material.brand }} - {{ material.color }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ material.type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    {{ material.width }} × {{ material.height }} × {{ material.thickness }}mm
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    {{ formatCurrency(material.price_per_sheet) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ material.projects_count || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            material.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                        ]"
                                    >
                                        {{ material.active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                    <Link
                                        :href="route('admin.materials.edit', material.id)"
                                        class="text-primary-600 hover:text-primary-900"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        @click="toggleActive(material.id)"
                                        class="text-gray-600 hover:text-gray-900"
                                    >
                                        {{ material.active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                    <button
                                        @click="deleteMaterial(material.id)"
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
    materials: Object,
});

const toggleActive = (id) => {
    router.post(route('admin.materials.toggle-active', id));
};

const deleteMaterial = (id) => {
    if (confirm('Deseja realmente excluir este material?')) {
        router.delete(route('admin.materials.destroy', id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value || 0);
};
</script>
