<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const INACTIVITY_LIMIT = 270000;
let inactivityTimer: ReturnType<typeof setTimeout> | null = null;
const showExpiredAlert = ref(false);
const countdown = ref(30);
let countdownInterval: ReturnType<typeof setInterval> | null = null;

const resetTimer = () => {
    if (showExpiredAlert.value) {
        showExpiredAlert.value = false;
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
    }

    if (inactivityTimer) clearTimeout(inactivityTimer);
    
    inactivityTimer = setTimeout(() => {
        showExpiredAlert.value = true;
        countdown.value = 30;
        
        countdownInterval = setInterval(() => {
            countdown.value--;
            if (countdown.value <= 0) {
                if (countdownInterval) clearInterval(countdownInterval);
                router.post('/logout');
            }
        }, 1000);
    }, INACTIVITY_LIMIT);
};

onMounted(() => {
    resetTimer();
    const events = ['mousemove', 'keydown', 'click', 'scroll'];
    events.forEach(event => window.addEventListener(event, resetTimer, { passive: true }));
});

onUnmounted(() => {
    if (inactivityTimer) clearTimeout(inactivityTimer);
    if (countdownInterval) clearInterval(countdownInterval);
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
            class="fixed top-5 left-1/2 transform -translate-x-1/2 z-50 px-6 py-4 rounded-lg shadow-xl bg-sidebar text-sidebar-foreground border border-sidebar-border max-w-sm w-full mx-4"
        >
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-500 shrink-0 mt-0.5 animate-pulse"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <div class="flex-1 space-y-1">
                    <p class="font-semibold text-sm">Session Expiring</p>
                    <p class="text-xs text-muted-foreground leading-normal">
                        You will be logged out in <span class="font-bold text-destructive font-mono text-sm">{{ countdown }}</span> seconds due to inactivity. Move your mouse or type to stay logged in.
                    </p>
                </div>
            </div>
        </div>
    </transition>
</template>
