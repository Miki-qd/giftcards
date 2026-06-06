<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const INACTIVITY_LIMIT = 300000;
let inactivityTimer: ReturnType<typeof setTimeout> | null = null;
const showExpiredAlert = ref(false);

const resetTimer = () => {
    if (showExpiredAlert.value) return;

    if (inactivityTimer) clearTimeout(inactivityTimer);
    
    inactivityTimer = setTimeout(() => {
        showExpiredAlert.value = true;
        
        setTimeout(() => {
            router.post('/logout');
        }, 2500);
    }, INACTIVITY_LIMIT);
};

onMounted(() => {
    resetTimer();
    const events = ['mousemove', 'keydown', 'click', 'scroll'];
    events.forEach(event => window.addEventListener(event, resetTimer, { passive: true }));
});

onUnmounted(() => {
    if (inactivityTimer) clearTimeout(inactivityTimer);
    const events = ['mousemove', 'keydown', 'click', 'scroll'];
    events.forEach(event => window.removeEventListener(event, resetTimer));
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>

    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 -translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-4"
    >
        <div 
            v-if="showExpiredAlert" 
            class="fixed top-5 left-1/2 transform -translate-x-1/2 z-50 px-6 py-4 rounded-lg shadow-xl bg-sidebar text-sidebar-foreground border border-sidebar-border"
        >
            <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-destructive"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <p class="font-semibold text-sm">Session Expired: You have been logged out due to inactivity.</p>
            </div>
        </div>
    </transition>
</template>
