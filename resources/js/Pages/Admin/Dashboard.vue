<template>
    <Head title="Admin Dashboard" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Painel Administrativo</h1>
                    <p class="mt-2 text-gray-600">Visão geral do sistema</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Users Stats -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-500">Usuários</h3>
                            <svg class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <div class="text-2xl font-bold text-gray-900">{{ stats.users.total }}</div>
                            <div class="text-xs text-gray-500">
                                <div>{{ stats.users.regular }} regulares</div>
                                <div>{{ stats.users.professionals }} profissionais</div>
                                <div>{{ stats.users.admins }} admins</div>
                            </div>
                            <div class="text-xs text-green-600">+{{ stats.users.recent }} últimos 7 dias</div>
                        </div>
                    </div>

                    <!-- Projects Stats -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-500">Projetos</h3>
                            <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <div class="text-2xl font-bold text-gray-900">{{ stats.projects.total }}</div>
                            <div class="text-xs text-gray-500">
                                <div>{{ stats.projects.pending }} pendentes</div>
                                <div>{{ stats.projects.processing }} processando</div>
                                <div>{{ stats.projects.completed }} concluídos</div>
                            </div>
                            <div class="text-xs text-green-600">+{{ stats.projects.recent }} últimos 7 dias</div>
                        </div>
                    </div>

                    <!-- Orders Stats -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-500">Pedidos</h3>
                            <svg class="h-8 w-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <div class="text-2xl font-bold text-gray-900">{{ stats.orders.total }}</div>
                            <div class="text-xs text-gray-500">
                                <div>{{ stats.orders.pending }} pendentes</div>
                                <div>{{ stats.orders.in_progress }} em progresso</div>
                                <div>{{ stats.orders.completed }} concluídos</div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Stats -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-500">Receita (Taxa 15%)</h3>
                            <svg class="h-8 w-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.orders.total_revenue) }}</div>
                            <div class="text-xs text-gray-500">
                                <div>De {{ stats.orders.paid + stats.orders.in_progress + stats.orders.completed }} pedidos pagos</div>
                            </div>
                        </div>
                    </div>

                    <!-- Professionals Stats -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-500">Profissionais</h3>
                            <svg class="h-8 w-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <div class="text-2xl font-bold text-gray-900">{{ stats.professionals.total }}</div>
                            <div class="text-xs text-gray-500">
                                <div>{{ stats.professionals.verified }} verificados</div>
                                <div>{{ stats.professionals.active }} ativos</div>
                                <div class="text-yellow-600">{{ stats.professionals.pending_verification }} aguardando</div>
                            </div>
                        </div>
                    </div>

                    <!-- Templates Stats -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-500">Templates</h3>
                            <svg class="h-8 w-8 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <div class="text-2xl font-bold text-gray-900">{{ stats.templates.total }}</div>
                            <div class="text-xs text-gray-500">
                                <div>{{ stats.templates.active }} ativos</div>
                            </div>
                        </div>
                    </div>

                    <!-- Offers Stats -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-500">Ofertas</h3>
                            <svg class="h-8 w-8 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <div class="text-2xl font-bold text-gray-900">{{ stats.offers.total }}</div>
                            <div class="text-xs text-gray-500">
                                <div>{{ stats.offers.pending }} pendentes</div>
                                <div>{{ stats.offers.accepted }} aceitas</div>
                                <div>{{ stats.offers.rejected }} rejeitadas</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <Link :href="route('admin.users.index')" class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg shadow-sm p-6 text-white hover:shadow-md transition">
                        <h3 class="text-lg font-semibold mb-2">Gerenciar Usuários</h3>
                        <p class="text-sm opacity-90">Visualizar, editar e gerenciar contas de usuários</p>
                    </Link>

                    <Link :href="route('admin.professionals.index')" class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-lg shadow-sm p-6 text-white hover:shadow-md transition">
                        <h3 class="text-lg font-semibold mb-2">Verificar Profissionais</h3>
                        <p class="text-sm opacity-90">{{ stats.professionals.pending_verification }} profissionais aguardando verificação</p>
                    </Link>

                    <Link :href="route('admin.templates.index')" class="bg-gradient-to-br from-pink-500 to-pink-700 rounded-lg shadow-sm p-6 text-white hover:shadow-md transition">
                        <h3 class="text-lg font-semibold mb-2">Templates & Materiais</h3>
                        <p class="text-sm opacity-90">Gerenciar templates e materiais do sistema</p>
                    </Link>
                </div>

                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Users -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Usuários Recentes</h3>
                        <div class="space-y-3">
                            <div v-for="user in recentUsers" :key="user.id" class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                <div>
                                    <div class="font-medium text-sm text-gray-900">{{ user.name }}</div>
                                    <div class="text-xs text-gray-500">{{ user.email }}</div>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">{{ user.role }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Projects -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Projetos Recentes</h3>
                        <div class="space-y-3">
                            <div v-for="project in recentProjects" :key="project.id" class="py-2 border-b border-gray-100 last:border-0">
                                <div class="font-medium text-sm text-gray-900">{{ project.name }}</div>
                                <div class="text-xs text-gray-500">{{ project.user?.name }} • {{ project.template?.name }}</div>
                                <span
                                    :class="[
                                        'inline-block mt-1 text-xs px-2 py-1 rounded-full',
                                        project.status === 'completed' ? 'bg-green-100 text-green-800' :
                                        project.status === 'processing' ? 'bg-blue-100 text-blue-800' :
                                        'bg-yellow-100 text-yellow-800'
                                    ]"
                                >
                                    {{ project.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pedidos Recentes</h3>
                        <div class="space-y-3">
                            <div v-for="order in recentOrders" :key="order.id" class="py-2 border-b border-gray-100 last:border-0">
                                <div class="font-medium text-sm text-gray-900">{{ formatCurrency(order.amount) }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ order.user?.name }} → {{ order.professional?.user?.name }}
                                </div>
                                <span
                                    :class="[
                                        'inline-block mt-1 text-xs px-2 py-1 rounded-full',
                                        order.status === 'completed' ? 'bg-green-100 text-green-800' :
                                        order.status === 'paid' ? 'bg-blue-100 text-blue-800' :
                                        'bg-yellow-100 text-yellow-800'
                                    ]"
                                >
                                    {{ order.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    stats: Object,
    recentUsers: Array,
    recentProjects: Array,
    recentOrders: Array,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};
</script>
