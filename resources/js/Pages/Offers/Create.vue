<template>
    <Head title="Criar Proposta" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Criar Proposta</h1>
                    <p class="mt-2 text-gray-600">Envie sua proposta para o cliente</p>
                </div>

                <!-- Project Summary -->
                <div v-if="project" class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Projeto</h2>
                    <div class="space-y-2">
                        <p><span class="font-medium">Nome:</span> {{ project.name }}</p>
                        <p><span class="font-medium">Template:</span> {{ project.template?.name }}</p>
                        <p><span class="font-medium">Material:</span> {{ project.material?.name }}</p>
                        <p><span class="font-medium">Peças:</span> {{ project.pieces?.length || 0 }}</p>
                        <p v-if="project.estimated_price"><span class="font-medium">Preço estimado:</span> {{ formatCurrency(project.estimated_price) }}</p>
                    </div>
                </div>

                <!-- Offer Form -->
                <div class="bg-white rounded-lg shadow-sm p-8">
                    <form @submit.prevent="submit">
                        <input type="hidden" v-model="form.project_id">

                        <div class="mb-6">
                            <InputLabel for="price" value="Valor da Proposta (R$) *" />
                            <TextInput
                                id="price"
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                placeholder="0.00"
                            />
                            <InputError :message="form.errors.price" />
                        </div>

                        <div class="mb-6">
                            <InputLabel for="delivery_days" value="Prazo de Entrega (dias) *" />
                            <TextInput
                                id="delivery_days"
                                v-model="form.delivery_days"
                                type="number"
                                min="1"
                                max="365"
                                required
                                placeholder="30"
                            />
                            <InputError :message="form.errors.delivery_days" />
                        </div>

                        <div class="mb-6">
                            <InputLabel for="message" value="Mensagem/Descrição *" />
                            <textarea
                                id="message"
                                v-model="form.message"
                                rows="6"
                                required
                                placeholder="Descreva sua proposta, experiência e como você planeja executar o projeto..."
                                class="w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm"
                            ></textarea>
                            <InputError :message="form.errors.message" />
                            <p class="mt-1 text-xs text-gray-500">{{ form.message.length }}/1000 caracteres</p>
                        </div>

                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('offers.index')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition"
                            >
                                Cancelar
                            </Link>
                            <PrimaryButton type="submit" :disabled="form.processing">
                                Enviar Proposta
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
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    project: Object,
});

const form = useForm({
    project_id: props.project?.id || null,
    price: '',
    delivery_days: 30,
    message: '',
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

const submit = () => {
    form.post(route('offers.store'));
};
</script>
