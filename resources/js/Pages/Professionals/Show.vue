<template>
    <Head :title="professional.business_name" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Back Button -->
                <div class="mb-6">
                    <Link :href="route('professionals.index')" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Voltar para Profissionais
                    </Link>
                </div>

                <!-- Professional Header -->
                <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-6">
                            <!-- Avatar -->
                            <div class="w-24 h-24 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-primary-700 font-bold text-3xl">
                                    {{ professional.business_name.charAt(0).toUpperCase() }}
                                </span>
                            </div>

                            <!-- Info -->
                            <div>
                                <div class="flex items-center space-x-3 mb-2">
                                    <h1 class="text-3xl font-bold text-gray-900">{{ professional.business_name }}</h1>
                                    <span
                                        v-if="professional.verified"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
                                    >
                                        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Verificado
                                    </span>
                                </div>
                                <p class="text-lg text-gray-600 mb-3">{{ professional.specialty }}</p>

                                <!-- Stats -->
                                <div class="flex items-center space-x-6 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <span class="font-semibold">{{ professional.rating?.toFixed(1) || '0.0' }}</span>
                                        <span class="ml-1">({{ professional.total_reviews || 0 }} avaliações)</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ professional.total_jobs || 0 }} trabalhos concluídos</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        </svg>
                                        <span>Raio de atendimento: {{ professional.service_radius_km }} km</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Button (if owner) -->
                        <div v-if="$page.props.auth.user.id === professional.user_id">
                            <Link
                                :href="route('professionals.edit', professional.id)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                            >
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Editar Perfil
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Details -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- About -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Sobre</h2>
                            <p class="text-gray-600 whitespace-pre-line">{{ professional.description }}</p>
                        </div>

                        <!-- Certifications -->
                        <div v-if="professional.certifications?.length > 0" class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Certificações</h2>
                            <ul class="space-y-2">
                                <li v-for="(cert, index) in professional.certifications" :key="index" class="flex items-center text-gray-600">
                                    <svg class="h-5 w-5 text-primary-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                    </svg>
                                    {{ cert }}
                                </li>
                            </ul>
                        </div>

                        <!-- Equipment -->
                        <div v-if="professional.equipment?.length > 0" class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Equipamentos</h2>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="(equip, index) in professional.equipment"
                                    :key="index"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700"
                                >
                                    {{ equip }}
                                </span>
                            </div>
                        </div>

                        <!-- Portfolio Link -->
                        <div v-if="professional.portfolio_url" class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Portfólio</h2>
                            <a
                                :href="professional.portfolio_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center text-primary-600 hover:text-primary-700"
                            >
                                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Ver Portfólio Online
                            </a>
                        </div>
                    </div>

                    <!-- Right Column - Contact & Actions -->
                    <div class="space-y-6">
                        <!-- Contact Card -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Entre em Contato</h3>
                            <div class="space-y-3">
                                <div class="flex items-center text-gray-600">
                                    <svg class="h-5 w-5 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>{{ professional.user?.name }}</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <svg class="h-5 w-5 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ professional.user?.email }}</span>
                                </div>
                                <div v-if="professional.user?.profile?.phone" class="flex items-center text-gray-600">
                                    <svg class="h-5 w-5 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <span>{{ professional.user.profile.phone }}</span>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Status</span>
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                                            professional.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                        ]"
                                    >
                                        {{ professional.active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- CTA - Request Quote (placeholder) -->
                        <div v-if="$page.props.auth.user.role === 'user'" class="bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg shadow-lg p-6 text-white">
                            <h3 class="text-lg font-semibold mb-2">Precisa de um orçamento?</h3>
                            <p class="text-sm mb-4 opacity-90">Entre em contato diretamente com o profissional ou solicite um orçamento através dos seus projetos.</p>
                            <Link
                                :href="route('projects.index')"
                                class="block w-full text-center px-4 py-2 bg-white text-primary-700 font-semibold rounded-md hover:bg-gray-100 transition"
                            >
                                Ver Meus Projetos
                            </Link>
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
    professional: Object,
});
</script>
