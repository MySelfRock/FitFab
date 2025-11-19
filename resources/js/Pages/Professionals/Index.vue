<template>
    <Head title="Profissionais" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Encontre Profissionais</h1>
                    <p class="mt-2 text-gray-600">Conecte-se com marceneiros e fabricantes de móveis na sua região</p>
                </div>

                <!-- Search Filters -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <form @submit.prevent="search" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Location Search -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Sua Localização
                                </label>
                                <button
                                    type="button"
                                    @click="getCurrentLocation"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50 transition"
                                >
                                    <svg v-if="loadingLocation" class="animate-spin h-4 w-4 inline-block mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="h-4 w-4 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ form.lat ? 'Localização Obtida' : 'Usar Minha Localização' }}
                                </button>
                            </div>

                            <!-- Radius -->
                            <div>
                                <label for="radius" class="block text-sm font-medium text-gray-700 mb-1">
                                    Raio (km)
                                </label>
                                <select
                                    id="radius"
                                    v-model="form.radius"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                >
                                    <option :value="10">10 km</option>
                                    <option :value="25">25 km</option>
                                    <option :value="50">50 km</option>
                                    <option :value="100">100 km</option>
                                    <option :value="200">200 km</option>
                                </select>
                            </div>

                            <!-- Specialty -->
                            <div>
                                <label for="specialty" class="block text-sm font-medium text-gray-700 mb-1">
                                    Especialidade
                                </label>
                                <select
                                    id="specialty"
                                    v-model="form.specialty"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                >
                                    <option value="">Todas</option>
                                    <option value="Móveis Planejados">Móveis Planejados</option>
                                    <option value="Móveis Corporativos">Móveis Corporativos</option>
                                    <option value="Design Personalizado">Design Personalizado</option>
                                    <option value="Marcenaria Geral">Marcenaria Geral</option>
                                </select>
                            </div>

                            <!-- Verified Only -->
                            <div class="flex items-end">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="form.verified"
                                        class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Apenas Verificados</span>
                                </label>
                            </div>
                        </div>

                        <!-- Search Button -->
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 transition"
                            >
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Buscar Profissionais
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Professionals Grid -->
                <div v-if="professionals.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="professional in professionals.data"
                        :key="professional.id"
                        class="bg-white rounded-lg shadow-sm hover:shadow-md transition border border-gray-200 overflow-hidden"
                    >
                        <div class="p-6">
                            <!-- Professional Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                                        <span class="text-primary-700 font-semibold text-lg">
                                            {{ professional.business_name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ professional.business_name }}</h3>
                                        <p class="text-sm text-gray-500">{{ professional.specialty }}</p>
                                    </div>
                                </div>
                                <span
                                    v-if="professional.verified"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Verificado
                                </span>
                            </div>

                            <!-- Description -->
                            <p class="text-sm text-gray-600 mb-4 line-clamp-3">{{ professional.description }}</p>

                            <!-- Stats -->
                            <div class="flex items-center space-x-4 mb-4 text-sm text-gray-500">
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    <span>{{ professional.rating?.toFixed(1) || '0.0' }}</span>
                                    <span class="ml-1">({{ professional.total_reviews || 0 }})</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ professional.total_jobs || 0 }} trabalhos</span>
                                </div>
                            </div>

                            <!-- Distance (if geolocation search) -->
                            <div v-if="professional.distance" class="mb-4">
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <span>{{ professional.distance?.toFixed(1) }} km de distância</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <Link
                                    :href="route('professionals.show', professional.id)"
                                    class="flex-1 text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                                >
                                    Ver Perfil
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12 bg-white rounded-lg shadow-sm">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum profissional encontrado</h3>
                    <p class="mt-1 text-sm text-gray-500">Tente ajustar os filtros de busca ou aumentar o raio de procura.</p>
                </div>

                <!-- Pagination -->
                <div v-if="professionals.data.length > 0 && (professionals.prev_page_url || professionals.next_page_url)" class="mt-8 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <Link
                            v-if="professionals.prev_page_url"
                            :href="professionals.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="professionals.next_page_url"
                            :href="professionals.next_page_url"
                            class="relative inline-flex items-center px-4 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Próximo
                        </Link>
                    </nav>
                </div>

                <!-- Become Professional CTA -->
                <div v-if="$page.props.auth.user.role === 'user'" class="mt-12 bg-gradient-to-r from-primary-500 to-primary-700 rounded-lg shadow-lg p-8 text-center text-white">
                    <h2 class="text-2xl font-bold mb-2">É um profissional?</h2>
                    <p class="mb-6">Cadastre-se e comece a receber propostas de trabalho na sua região!</p>
                    <Link
                        :href="route('professionals.create')"
                        class="inline-flex items-center px-6 py-3 bg-white text-primary-700 font-semibold rounded-md hover:bg-gray-100 transition"
                    >
                        Tornar-se Profissional
                    </Link>
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

const form = ref({
    lat: props.filters.lat || null,
    lng: props.filters.lng || null,
    radius: props.filters.radius || 50,
    specialty: props.filters.specialty || '',
    verified: props.filters.verified || false,
});

const loadingLocation = ref(false);

const getCurrentLocation = () => {
    if (!navigator.geolocation) {
        alert('Geolocalização não é suportada pelo seu navegador.');
        return;
    }

    loadingLocation.value = true;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.value.lat = position.coords.latitude;
            form.value.lng = position.coords.longitude;
            loadingLocation.value = false;
        },
        (error) => {
            console.error('Erro ao obter localização:', error);
            alert('Não foi possível obter sua localização. Verifique as permissões do navegador.');
            loadingLocation.value = false;
        }
    );
};

const search = () => {
    router.get(route('professionals.index'), {
        lat: form.value.lat,
        lng: form.value.lng,
        radius: form.value.radius,
        specialty: form.value.specialty,
        verified: form.value.verified,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>
