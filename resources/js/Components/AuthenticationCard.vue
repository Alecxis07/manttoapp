<script setup>
import ThemeToggle from '@/Components/ThemeToggle.vue';
</script>

<template>
    <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[var(--mantto-canvas)]">
        <div class="absolute end-4 top-4 sm:end-6 sm:top-6">
            <ThemeToggle />
        </div>

        <div>
            <slot name="logo" />
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-[var(--mantto-surface)] shadow-md overflow-hidden sm:rounded-lg border border-steel-200/80 dark:border-steel-800">
            <slot />
        </div>
    </div>
</template>
