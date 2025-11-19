<template>
    <Head title="Novo Projeto" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Criar Novo Projeto</h1>
                    <p class="mt-2 text-gray-600">Preencha as informações para gerar seu projeto de móvel</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-8">
                    <form @submit.prevent="submit">
                        <!-- Project Name -->
                        <div class="mb-6">
                            <InputLabel for="name" value="Nome do Projeto" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="Ex: Estante Sala de Estar"
                                autofocus
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <!-- Template Selection -->
                        <div class="mb-6">
                            <InputLabel value="Selecione o Template" />
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div
                                    v-for="template in templates"
                                    :key="template.id"
                                    @click="selectTemplate(template)"
                                    :class="[
                                        'relative rounded-lg border-2 p-4 cursor-pointer hover:border-primary-400 transition',
                                        form.template_id === template.id ? 'border-primary-600 bg-primary-50' : 'border-gray-200'
                                    ]"
                                >
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                                                <svg class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <h3 class="text-sm font-medium text-gray-900">{{ template.name }}</h3>
                                            <p class="mt-1 text-xs text-gray-500">{{ template.description }}</p>
                                        </div>
                                        <div v-if="form.template_id === template.id" class="ml-2">
                                            <svg class="h-5 w-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <InputError :message="form.errors.template_id" />
                        </div>

                        <!-- Material Selection -->
                        <div class="mb-6">
                            <InputLabel value="Selecione o Material" />
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div
                                    v-for="material in materials"
                                    :key="material.id"
                                    @click="form.material_id = material.id"
                                    :class="[
                                        'relative rounded-lg border-2 p-4 cursor-pointer hover:border-primary-400 transition',
                                        form.material_id === material.id ? 'border-primary-600 bg-primary-50' : 'border-gray-200'
                                    ]"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-sm font-medium text-gray-900">{{ material.name }}</h3>
                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ material.sheet_width_mm }}x{{ material.sheet_height_mm }}mm
                                            </p>
                                            <p v-if="material.price_per_sheet" class="mt-1 text-sm font-semibold text-primary-600">
                                                {{ formatCurrency(material.price_per_sheet) }}/chapa
                                            </p>
                                        </div>
                                        <div v-if="form.material_id === material.id">
                                            <svg class="h-5 w-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <InputError :message="form.errors.material_id" />
                        </div>

                        <!-- Custom Options -->
                        <div v-if="selectedTemplate" class="mb-6">
                            <InputLabel value="Opções de Personalização" />
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div v-for="(value, key) in selectedTemplate.default_options" :key="key">
                                    <label :for="`option-${key}`" class="block text-sm font-medium text-gray-700 capitalize mb-1">
                                        {{ formatOptionLabel(key) }}
                                    </label>
                                    <input
                                        :id="`option-${key}`"
                                        v-model="form.options[key]"
                                        type="number"
                                        :placeholder="`${value}`"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                    />
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Todas as medidas em milímetros (mm)</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('projects.index')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition"
                            >
                                Cancelar
                            </Link>
                            <PrimaryButton type="submit" :disabled="form.processing || !form.template_id || !form.material_id">
                                Criar Projeto
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    templates: Array,
    materials: Array,
});

const form = useForm({
    name: '',
    template_id: null,
    material_id: null,
    options: {},
});

const selectedTemplate = computed(() => {
    return props.templates.find(t => t.id === form.template_id);
});

const selectTemplate = (template) => {
    form.template_id = template.id;
    // Initialize options with default values
    form.options = { ...template.default_options };
};

const formatOptionLabel = (key) => {
    const labels = {
        width: 'Largura',
        height: 'Altura',
        depth: 'Profundidade',
        shelves: 'Prateleiras',
        drawers: 'Gavetas',
        doors: 'Portas',
    };
    return labels[key] || key;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

const submit = () => {
    form.post(route('projects.store'));
};
</script>
