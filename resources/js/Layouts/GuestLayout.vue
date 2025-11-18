<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-primary-500 to-primary-700">
        <div class="mb-8">
            <Link href="/" class="flex items-center space-x-2">
                <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center">
                    <span class="text-2xl font-bold text-primary-600">F</span>
                </div>
                <span class="text-3xl font-bold text-white">FitFab</span>
            </Link>
        </div>

        <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-2xl overflow-hidden sm:rounded-2xl">
            <slot />
        </div>

        <div class="mt-6 text-center text-white text-sm">
            <p>&copy; {{ new Date().getFullYear() }} FitFab. Todos os direitos reservados.</p>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
