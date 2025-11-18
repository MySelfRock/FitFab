<template>
    <Head :title="`Usuário: ${user.name}`" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <Link :href="route('admin.users.index')" class="text-sm text-gray-600 hover:text-gray-900 mb-4 inline-block">
                        ← Voltar para Usuários
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-900">{{ user.name }}</h1>
                    <p class="mt-2 text-gray-600">{{ user.email }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- User Info -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-semibold mb-4">Informações do Usuário</h2>
                            <dl class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nome</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ user.name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ user.email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Função</dt>
                                    <dd class="mt-1">
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
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Cadastro</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user.created_at) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Stats -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-semibold mb-4">Estatísticas</h2>
                            <dl class="grid grid-cols-3 gap-4">
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <dt class="text-sm text-gray-500">Projetos</dt>
                                    <dd class="mt-1 text-2xl font-bold text-gray-900">{{ stats.projects_count }}</dd>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <dt class="text-sm text-gray-500">Pedidos</dt>
                                    <dd class="mt-1 text-2xl font-bold text-gray-900">{{ stats.orders_count }}</dd>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <dt class="text-sm text-gray-500">Total Gasto</dt>
                                    <dd class="mt-1 text-2xl font-bold text-gray-900">{{ formatCurrency(stats.total_spent) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Professional Stats (if applicable) -->
                        <div v-if="user.role === 'professional' && user.professional" class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-semibold mb-4">Estatísticas como Profissional</h2>
                            <dl class="grid grid-cols-3 gap-4">
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <dt class="text-sm text-gray-500">Pedidos Recebidos</dt>
                                    <dd class="mt-1 text-2xl font-bold text-gray-900">{{ stats.professional_orders || 0 }}</dd>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <dt class="text-sm text-gray-500">Receita Total</dt>
                                    <dd class="mt-1 text-2xl font-bold text-gray-900">{{ formatCurrency(stats.professional_revenue || 0) }}</dd>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <dt class="text-sm text-gray-500">Ofertas Pendentes</dt>
                                    <dd class="mt-1 text-2xl font-bold text-gray-900">{{ stats.pending_offers || 0 }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-6">
                        <!-- Change Role -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-semibold mb-4">Alterar Função</h3>
                            <form @submit.prevent="updateRole">
                                <select v-model="roleForm.role" class="w-full border-gray-300 rounded-md mb-4">
                                    <option value="user">Usuário</option>
                                    <option value="professional">Profissional</option>
                                    <option value="admin">Administrador</option>
                                </select>
                                <button type="submit" class="w-full px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                                    Atualizar Função
                                </button>
                            </form>
                        </div>

                        <!-- Actions -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-semibold mb-4">Ações</h3>
                            <div class="space-y-2">
                                <button
                                    @click="toggleStatus"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
                                >
                                    {{ user.email_verified_at ? 'Suspender' : 'Ativar' }} Conta
                                </button>
                                <button
                                    @click="deleteUser"
                                    class="w-full px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700"
                                >
                                    Excluir Usuário
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
    user: Object,
    stats: Object,
});

const roleForm = ref({
    role: props.user.role,
});

const updateRole = () => {
    if (confirm('Deseja realmente alterar a função deste usuário?')) {
        router.post(route('admin.users.update-role', props.user.id), roleForm.value);
    }
};

const toggleStatus = () => {
    const action = props.user.email_verified_at ? 'suspender' : 'ativar';
    if (confirm(`Deseja realmente ${action} este usuário?`)) {
        router.post(route('admin.users.toggle-status', props.user.id));
    }
};

const deleteUser = () => {
    if (confirm('ATENÇÃO: Esta ação é irreversível! Deseja realmente excluir este usuário?')) {
        router.delete(route('admin.users.destroy', props.user.id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR');
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value || 0);
};
</script>
