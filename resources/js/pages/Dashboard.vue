<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { CreditCard, DollarSign, Activity } from '@lucide/vue';

interface CardStatuses {
    labels: string[];
    data: number[];
}

interface SecurityStats {
    status: string;
    encryption: boolean;
    databaseProtection: boolean;
    loginProtection: boolean;
    suspiciousActivity: boolean;
}

const props = defineProps<{
    totalCards: number;
    totalBalance: string | number;
    activeCards: number;
    cardStatuses: CardStatuses;
    securityStats: SecurityStats;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

// Left Donut Chart Computations
const totalStatuses = computed(() => {
    return props.cardStatuses.data.reduce((sum, val) => sum + val, 0) || 1;
});

const segments = computed(() => {
    let accumulatedPercent = 0;
    const colors = [
        'stroke-emerald-500 dark:stroke-emerald-500',
        'stroke-blue-500 dark:stroke-blue-500',
        'stroke-destructive dark:stroke-destructive',
    ];
    const hoverColors = [
        'hover:stroke-emerald-400',
        'hover:stroke-blue-400',
        'hover:stroke-red-400',
    ];
    
    return props.cardStatuses.data.map((val, idx) => {
        const percent = (val / totalStatuses.value) * 100;
        const offset = 100 - accumulatedPercent;
        accumulatedPercent += percent;
        
        return {
            label: props.cardStatuses.labels[idx],
            val,
            percent,
            strokeDasharray: `${percent} ${100 - percent}`,
            strokeDashoffset: offset,
            colorClass: colors[idx] || 'stroke-muted',
            hoverClass: hoverColors[idx] || '',
            fillClass: ['bg-emerald-500', 'bg-blue-500', 'bg-destructive'][idx]
        };
    });
});


</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4 mt-8">
        <!-- Statistic Cards Section -->
        <div class="grid gap-4 md:grid-cols-3">
            <div class="flex flex-col gap-2 rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-sidebar">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">Total Gift Cards</h3>
                    <CreditCard class="h-4 w-4 text-muted-foreground" />
                </div>
                <div class="text-2xl font-bold">{{ props.totalCards }}</div>
            </div>
            
            <div class="flex flex-col gap-2 rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-sidebar">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">Total Balance</h3>
                    <DollarSign class="h-4 w-4 text-muted-foreground" />
                </div>
                <div class="text-2xl font-bold">${{ parseFloat(props.totalBalance as string).toFixed(2) }}</div>
            </div>

            <div class="flex flex-col gap-2 rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-sidebar">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">Number of Active Cards</h3>
                    <Activity class="h-4 w-4 text-muted-foreground" />
                </div>
                <div class="text-2xl font-bold">{{ props.activeCards }}</div>
            </div>
        </div>

        <!-- Analytics Charts Grid Section -->
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Left Donut Chart: Card Status Breakdown -->
            <div class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 bg-card shadow-sm dark:border-sidebar-border dark:bg-sidebar p-6">
                <div>
                    <h3 class="text-lg font-medium">Card Status Breakdown</h3>
                    <p class="text-sm text-muted-foreground">Distribution of active, redeemed, and expired cards</p>
                </div>
                
                <div class="flex flex-col items-center justify-center py-6 gap-6 md:flex-row">
                    <!-- Donut SVG -->
                    <div class="relative w-80 h-80">
                        <svg viewBox="0 0 42 42" class="w-full h-full transform -rotate-90">
                            <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="currentColor" class="text-secondary/20" stroke-width="3.5"></circle>
                            <!-- Use v-show to prevent Vue 3 compilation error where v-if takes priority over v-for -->
                            <circle 
                                v-for="(seg, idx) in segments" 
                                v-show="seg.val > 0"
                                :key="idx"
                                cx="21" 
                                cy="21" 
                                r="15.91549430918954" 
                                fill="transparent" 
                                :class="[seg.colorClass, seg.hoverClass]" 
                                stroke-width="3.5" 
                                :stroke-dasharray="seg.strokeDasharray" 
                                :stroke-dashoffset="seg.strokeDashoffset"
                                stroke-linecap="round"
                                class="transition-all duration-300 cursor-pointer"
                            ></circle>
                        </svg>
                        <!-- Donut Inner Text -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-extrabold text-foreground">{{ props.totalCards }}</span>
                            <span class="text-[10px] uppercase text-muted-foreground tracking-wider font-semibold">Total Cards</span>
                        </div>
                    </div>

                    <!-- Donut Legend -->
                    <div class="flex flex-col gap-3">
                        <div v-for="(seg, idx) in segments" :key="idx" class="flex items-center gap-3">
                            <span class="w-3.5 h-3.5 rounded-full shadow-sm" :class="seg.fillClass"></span>
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-foreground">{{ seg.label }}</span>
                                <span class="text-[10px] text-muted-foreground font-mono">{{ seg.val }} cards ({{ seg.percent.toFixed(1) }}%)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Widget: System Security -->
            <div class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 bg-card shadow-sm dark:border-sidebar-border dark:bg-sidebar p-6">
                <div>
                    <h3 class="text-lg font-medium">System Security</h3>
                    <p class="text-sm text-muted-foreground">Overview of your application's security status</p>
                </div>

                <div class="flex flex-col gap-8 pt-6 h-full justify-center">
                    <!-- Secure Status Badge -->
                    <div class="flex flex-col items-center justify-center p-6 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                        <div class="h-16 w-16 rounded-full bg-emerald-500/20 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <span class="text-2xl font-bold text-emerald-500 uppercase tracking-widest">100% Secure</span>
                        <span class="text-sm text-muted-foreground mt-1">All protection systems are active</span>
                    </div>

                    <!-- Simplified Feature List -->
                    <div class="grid gap-4 bg-muted/30 dark:bg-zinc-900/30 p-5 rounded-lg border border-sidebar-border/50">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground">Data Encryption</span>
                            <span class="text-sm font-bold text-emerald-500" v-if="props.securityStats.encryption">ON</span>
                            <span class="text-sm font-bold text-destructive" v-else>OFF</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground">Database Protection</span>
                            <span class="text-sm font-bold text-emerald-500" v-if="props.securityStats.databaseProtection">ON</span>
                            <span class="text-sm font-bold text-destructive" v-else>OFF</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground">Login Protection</span>
                            <span class="text-sm font-bold text-emerald-500" v-if="props.securityStats.loginProtection">ON</span>
                            <span class="text-sm font-bold text-destructive" v-else>OFF</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
