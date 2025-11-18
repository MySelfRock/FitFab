<template>
    <Head title="Gerenciar Usuários" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Gerenciar Usuários</h1>
                        <p class="mt-2 text-gray-600">{{ users.total }} usuários no total</p>
                    </div>
                    <Link :href="route('admin.dashboard')" class="text-sm text-gray-600 hover:text-gray-900">
                        ← Voltar ao Dashboard
                    </Link>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <form @submit.prevent="search" class="flex gap-4">
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Buscar por nome ou email..."
                            class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                        />
                        <select
                            v-model="form.role"
                            class="border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                        >
                            <option value="">Todas as funções</option>
                            <option value="user">Usuários</option>
                            <option value="professional">Profissionais</option>
                            <option value="admin">Administradores</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                            Buscar
                        </button>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Função</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Projetos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pedidos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cadastro</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users.data" :key="user.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-sm text-gray-900">{{ user.name }}</div>
                                    <div class="text-xs text-gray-500">{{ user.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            user.role === 'admin' ? 'bg-red-100 text-red-800' :
                                            user.role === 'professional' ? 'bg-blue-100 text-blue-800' :
                                            'bg-gray-100 text-gray-800'
                                        ]"
                                    >
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ user.projects_count || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ user.orders_count || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    {{ formatDate(user.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <Link
                                        :href="route('admin.users.show', user.id)"
                                        class="text-primary-600 hover:text-primary-900"
                                    >
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.links && users.links.length > 3" class="mt-6 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <Link
                            v-for="(link, index) in users.links"
                            :key="index"
                            :href="link.url"
                            :class="[
                                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                link.active ? 'z-10 bg-primary-600 border-primary-600 text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                index === 0 ? 'rounded-l-md' : '',
                                index === users.links.length - 1 ? 'rounded-r-md' : ''
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
    users: Object,
    filters: Object,
});

const form = ref({
    search: props.filters?.search || '',
    role: props.filters?.role || '',
});

const search = () => {
    router.get(route('admin.users.index'), {
        search: form.value.search,
        role: form.value.role,
    }, {
        preserveState: true,
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR');
};
</script>
