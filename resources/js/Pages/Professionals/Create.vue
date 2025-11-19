<template>
    <Head title="Tornar-se Profissional" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Tornar-se Profissional</h1>
                    <p class="mt-2 text-gray-600">Crie seu perfil profissional e comece a receber propostas de trabalho</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-8">
                    <form @submit.prevent="submit">
                        <!-- Business Name -->
                        <div class="mb-6">
                            <InputLabel for="business_name" value="Nome do Negócio *" />
                            <TextInput
                                id="business_name"
                                v-model="form.business_name"
                                type="text"
                                required
                                placeholder="Ex: Marcenaria Silva"
                            />
                            <InputError :message="form.errors.business_name" />
                            <p class="mt-1 text-xs text-gray-500">Como sua empresa ou negócio é conhecid@</p>
                        </div>

                        <!-- Specialty -->
                        <div class="mb-6">
                            <InputLabel for="specialty" value="Especialidade *" />
                            <select
                                id="specialty"
                                v-model="form.specialty"
                                required
                                class="w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm"
                            >
                                <option value="">Selecione...</option>
                                <option value="Móveis Planejados">Móveis Planejados</option>
                                <option value="Móveis Corporativos">Móveis Corporativos</option>
                                <option value="Design Personalizado">Design Personalizado</option>
                                <option value="Marcenaria Geral">Marcenaria Geral</option>
                                <option value="Serraria">Serraria</option>
                                <option value="Fabricação sob Medida">Fabricação sob Medida</option>
                            </select>
                            <InputError :message="form.errors.specialty" />
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <InputLabel for="description" value="Descrição do Serviço *" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                required
                                placeholder="Descreva seus serviços, experiência e diferenciais..."
                                class="w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm"
                            ></textarea>
                            <InputError :message="form.errors.description" />
                            <p class="mt-1 text-xs text-gray-500">{{ form.description.length }}/1000 caracteres</p>
                        </div>

                        <!-- Portfolio URL -->
                        <div class="mb-6">
                            <InputLabel for="portfolio_url" value="Link do Portfólio" />
                            <TextInput
                                id="portfolio_url"
                                v-model="form.portfolio_url"
                                type="url"
                                placeholder="https://exemplo.com/portfolio"
                            />
                            <InputError :message="form.errors.portfolio_url" />
                            <p class="mt-1 text-xs text-gray-500">Opcional: Site, Instagram, Facebook, etc</p>
                        </div>

                        <!-- Location -->
                        <div class="mb-6">
                            <InputLabel value="Localização *" />
                            <p class="text-sm text-gray-600 mb-2">
                                Usaremos sua localização para conectá-lo com clientes próximos
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="lat" class="block text-sm text-gray-700 mb-1">Latitude</label>
                                    <input
                                        id="lat"
                                        v-model="form.lat"
                                        type="number"
                                        step="any"
                                        required
                                        readonly
                                        class="w-full border-gray-300 bg-gray-50 rounded-md shadow-sm"
                                        placeholder="-23.550520"
                                    />
                                    <InputError :message="form.errors.lat" />
                                </div>
                                <div>
                                    <label for="lng" class="block text-sm text-gray-700 mb-1">Longitude</label>
                                    <input
                                        id="lng"
                                        v-model="form.lng"
                                        type="number"
                                        step="any"
                                        required
                                        readonly
                                        class="w-full border-gray-300 bg-gray-50 rounded-md shadow-sm"
                                        placeholder="-46.633308"
                                    />
                                    <InputError :message="form.errors.lng" />
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="getCurrentLocation"
                                :disabled="loadingLocation"
                                class="mt-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition disabled:opacity-50"
                            >
                                <svg v-if="loadingLocation" class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                {{ loadingLocation ? 'Obtendo localização...' : 'Usar Minha Localização Atual' }}
                            </button>
                        </div>

                        <!-- Service Radius -->
                        <div class="mb-6">
                            <InputLabel for="service_radius_km" value="Raio de Atendimento (km) *" />
                            <div class="flex items-center space-x-4">
                                <input
                                    id="service_radius_km"
                                    v-model="form.service_radius_km"
                                    type="range"
                                    min="1"
                                    max="200"
                                    class="flex-1"
                                />
                                <span class="text-lg font-semibold text-gray-900 w-16 text-right">{{ form.service_radius_km }} km</span>
                            </div>
                            <InputError :message="form.errors.service_radius_km" />
                            <p class="mt-1 text-xs text-gray-500">Até que distância você atende clientes?</p>
                        </div>

                        <!-- Certifications -->
                        <div class="mb-6">
                            <InputLabel value="Certificações" />
                            <div class="space-y-2">
                                <div v-for="(cert, index) in form.certifications" :key="index" class="flex items-center space-x-2">
                                    <input
                                        v-model="form.certifications[index]"
                                        type="text"
                                        placeholder="Ex: SENAI - Marcenaria"
                                        class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                    />
                                    <button
                                        type="button"
                                        @click="removeCertification(index)"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="addCertification"
                                class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium"
                            >
                                + Adicionar Certificação
                            </button>
                        </div>

                        <!-- Equipment -->
                        <div class="mb-6">
                            <InputLabel value="Equipamentos" />
                            <div class="space-y-2">
                                <div v-for="(equip, index) in form.equipment" :key="index" class="flex items-center space-x-2">
                                    <input
                                        v-model="form.equipment[index]"
                                        type="text"
                                        placeholder="Ex: Serra Esquadrejadeira"
                                        class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                    />
                                    <button
                                        type="button"
                                        @click="removeEquipment(index)"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="addEquipment"
                                class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium"
                            >
                                + Adicionar Equipamento
                            </button>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Verificação Pendente</h3>
                                    <p class="mt-1 text-sm text-blue-700">
                                        Após criar seu perfil, ele passará por uma verificação. Você poderá receber propostas assim que for aprovado.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('dashboard')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition"
                            >
                                Cancelar
                            </Link>
                            <PrimaryButton type="submit" :disabled="form.processing">
                                Criar Perfil Profissional
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    business_name: '',
    specialty: '',
    description: '',
    portfolio_url: '',
    lat: null,
    lng: null,
    service_radius_km: 50,
    certifications: [],
    equipment: [],
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
            form.lat = position.coords.latitude;
            form.lng = position.coords.longitude;
            loadingLocation.value = false;
        },
        (error) => {
            console.error('Erro ao obter localização:', error);
            alert('Não foi possível obter sua localização. Verifique as permissões do navegador.');
            loadingLocation.value = false;
        }
    );
};

const addCertification = () => {
    form.certifications.push('');
};

const removeCertification = (index) => {
    form.certifications.splice(index, 1);
};

const addEquipment = () => {
    form.equipment.push('');
};

const removeEquipment = (index) => {
    form.equipment.splice(index, 1);
};

const submit = () => {
    // Filter out empty certifications and equipment
    form.certifications = form.certifications.filter(c => c.trim() !== '');
    form.equipment = form.equipment.filter(e => e.trim() !== '');

    form.post(route('professionals.store'));
};
</script>
