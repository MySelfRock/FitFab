<template>
    <GuestLayout>
        <Head title="Login" />

        <div class="mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Bem-vindo de volta!</h2>
            <p class="mt-1 text-sm text-gray-600">Entre na sua conta para continuar</p>
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="E-mail" />
                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
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
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
                    />
                    <span class="ml-2 text-sm text-gray-600">Lembrar de mim</span>
                </label>
            </div>

            <div class="flex items-center justify-between mt-6">
                <Link
                    href="/register"
                    class="text-sm text-primary-600 hover:text-primary-900 underline"
                >
                    Criar uma conta
                </Link>

                <PrimaryButton :disabled="form.processing">
                    Entrar
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

defineProps({
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>
