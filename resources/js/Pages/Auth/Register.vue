<template>
    <GuestLayout>
        <Head title="Criar Conta" />

        <div class="mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Criar sua conta</h2>
            <p class="mt-1 text-sm text-gray-600">Comece a criar seus projetos agora</p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Nome completo" />
                <TextInput
                    id="name"
                    type="text"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="João Silva"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="E-mail" />
                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="seu@email.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Senha" />
                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmar senha" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <Link
                    href="/login"
                    class="text-sm text-primary-600 hover:text-primary-900 underline"
                >
                    Já tem uma conta?
                </Link>

                <PrimaryButton :disabled="form.processing">
                    Criar conta
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
