<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface User {
    id: number;
    name: string;
}

interface LogEntry {
    id: number;
    user_id: number;
    action: string;
    card_number: string;
    description: string;
    properties: Record<string, unknown> | null;
    created_at: string;
    user: User | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedLogs {
    data: LogEntry[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
}

defineProps<{
    logs: PaginatedLogs;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'History',
                href: '/history',
            },
        ],
    },
});

function actionVariant(action: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (action) {
        case 'created':
            return 'default';
        case 'updated':
            return 'secondary';
        case 'deleted':
            return 'destructive';
        default:
            return 'outline';
    }
}

function formatDate(dateStr: string): string {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head title="History" />

    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <Heading title="History" description="Activity log of all gift card operations" />
            <div class="flex items-center gap-3">
                <a href="/history/export">
                    <Button variant="outline">Export CSV</Button>
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-sidebar-border/70 bg-muted/50 dark:border-sidebar-border">
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-muted-foreground">Date</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-muted-foreground">User</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-muted-foreground">Action</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-muted-foreground">Card Number</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-medium text-muted-foreground">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="5" class="px-4 py-12 text-center text-muted-foreground">
                                No activity logs yet. Operations on gift cards will appear here.
                            </td>
                        </tr>
                        <tr
                            v-for="log in logs.data"
                            :key="log.id"
                            class="border-b border-sidebar-border/70 transition-colors hover:bg-muted/30 dark:border-sidebar-border"
                        >
                            <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">
                                {{ formatDate(log.created_at) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                {{ log.user?.name ?? 'Unknown' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <Badge :variant="actionVariant(log.action)">
                                    {{ log.action }}
                                </Badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-sm">
                                {{ log.card_number }}
                            </td>
                            <td class="px-4 py-3">
                                {{ log.description }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="logs.last_page > 1"
                class="flex items-center justify-between border-t border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border"
            >
                <p class="text-sm text-muted-foreground">
                    Page {{ logs.current_page }} of {{ logs.last_page }} ({{ logs.total }} total)
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in logs.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm transition-colors"
                            :class="link.active
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'border-sidebar-border/70 hover:bg-muted/50 dark:border-sidebar-border'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-sm text-muted-foreground opacity-50"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
