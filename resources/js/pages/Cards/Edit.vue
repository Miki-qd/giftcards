<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Card {
    id: number;
    card_number: string;
    pin: string;
    activation_date: string;
    expiration_date: string;
    balance: string;
}

const props = defineProps<{
    card: Card;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Gift Cards',
                href: '/cards',
            },
            {
                title: 'Edit',
            },
        ],
    },
});

const formattedActivationDate = props.card.activation_date
    ? props.card.activation_date.slice(0, 16)
    : '';
const originalExpirationDate = props.card.expiration_date
    ? props.card.expiration_date.slice(0, 10)
    : '';

const expirationDate = ref(originalExpirationDate);
const selectedExpPreset = ref('custom');

function setExpPreset(preset: string) {
    selectedExpPreset.value = preset;
    if (preset !== 'custom') {
        const baseDate = new Date(originalExpirationDate);
        if (isNaN(baseDate.getTime())) return;
        
        let monthsToAdd = 0;
        if (preset === '1m') monthsToAdd = 1;
        else if (preset === '2m') monthsToAdd = 2;
        else if (preset === '3m') monthsToAdd = 3;
        else if (preset === '6m') monthsToAdd = 6;
        
        baseDate.setMonth(baseDate.getMonth() + monthsToAdd);
        
        const yyyy = baseDate.getFullYear();
        const mm = String(baseDate.getMonth() + 1).padStart(2, '0');
        const dd = String(baseDate.getDate()).padStart(2, '0');
        expirationDate.value = `${yyyy}-${mm}-${dd}`;
    }
}

function handleExpInput() {
    selectedExpPreset.value = 'custom';
}

// Balance section:
const originalBalance = Number(props.card.balance);
const balance = ref(props.card.balance);
const selectedBalanceAdd = ref(0);

function addBalance(amount: number) {
    selectedBalanceAdd.value = amount;
    balance.value = (originalBalance + amount).toFixed(2);
}

function selectCustomBalance() {
    selectedBalanceAdd.value = 0;
}

function handleBalanceInput() {
    selectedBalanceAdd.value = 0;
}
</script>

<template>
    <Head title="Edit Gift Card" />

    <div class="flex flex-col gap-6">
        <Heading title="Edit Gift Card" description="Update the gift card details" />

        <div class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
            <Form
                :action="`/cards/${card.id}`"
                method="put"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center h-8">
                            <Label for="card_number">Card Number</Label>
                        </div>
                        <Input
                            id="card_number"
                            name="card_number"
                            type="password"
                            placeholder="20-digit card number"
                            maxlength="20"
                            :default-value="card.card_number"
                            readonly
                            class="bg-muted/50 text-muted-foreground cursor-not-allowed select-none"
                            required
                        />
                        <InputError :message="errors.card_number" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center h-8">
                            <Label for="pin">PIN</Label>
                        </div>
                        <Input
                            id="pin"
                            name="pin"
                            type="password"
                            placeholder="4-digit PIN"
                            maxlength="4"
                            :default-value="card.pin"
                            readonly
                            class="bg-muted/50 text-muted-foreground cursor-not-allowed select-none"
                            required
                        />
                        <InputError :message="errors.pin" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center h-8">
                            <Label for="activation_date">Activation Date</Label>
                        </div>
                        <Input
                            id="activation_date"
                            name="activation_date"
                            type="datetime-local"
                            :default-value="formattedActivationDate"
                            readonly
                            class="bg-muted/50 text-muted-foreground cursor-not-allowed select-none"
                            required
                        />
                        <InputError :message="errors.activation_date" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between h-8">
                            <Label for="expiration_date">Expiration Date</Label>
                            <div class="inline-flex items-center gap-0.5 p-0.5 bg-secondary/80 dark:bg-secondary/40 rounded-lg border border-input/40">
                                <button
                                    type="button"
                                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200"
                                    :class="selectedExpPreset === '1m' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                    @click="setExpPreset('1m')"
                                >
                                    +1M
                                </button>
                                <button
                                    type="button"
                                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200"
                                    :class="selectedExpPreset === '2m' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                    @click="setExpPreset('2m')"
                                >
                                    +2M
                                </button>
                                <button
                                    type="button"
                                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200"
                                    :class="selectedExpPreset === '3m' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                    @click="setExpPreset('3m')"
                                >
                                    +3M
                                </button>
                                <button
                                    type="button"
                                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200"
                                    :class="selectedExpPreset === '6m' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                    @click="setExpPreset('6m')"
                                >
                                    +6M
                                </button>
                                <button
                                    type="button"
                                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200"
                                    :class="selectedExpPreset === 'custom' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                    @click="setExpPreset('custom')"
                                >
                                    Custom
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="expiration_date" :value="expirationDate" />
                        <Input
                            v-if="selectedExpPreset === 'custom'"
                            id="expiration_date"
                            v-model="expirationDate"
                            @input="handleExpInput"
                            type="date"
                            required
                            :min="originalExpirationDate"
                        />
                        <div v-else class="h-10 flex items-center px-3 text-sm font-medium text-muted-foreground bg-muted/40 rounded-md border border-input/30">
                            New Expiration:&nbsp;<span class="text-foreground font-semibold">{{ expirationDate }}</span>
                        </div>
                        <InputError :message="errors.expiration_date" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between h-8">
                            <Label for="balance">Balance ($)</Label>
                            <div class="inline-flex items-center gap-0.5 p-0.5 bg-secondary/80 dark:bg-secondary/40 rounded-lg border border-input/40">
                                <button
                                    v-for="amount in [5, 10, 20, 30, 50]"
                                    :key="amount"
                                    type="button"
                                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200"
                                    :class="selectedBalanceAdd === amount ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                    @click="addBalance(amount)"
                                >
                                    +${{ amount }}
                                </button>
                                <button
                                    type="button"
                                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all duration-200"
                                    :class="selectedBalanceAdd === 0 ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                    @click="selectCustomBalance"
                                >
                                    Custom
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="balance" :value="balance" />
                        <Input
                            v-if="selectedBalanceAdd === 0"
                            id="balance"
                            v-model="balance"
                            @input="handleBalanceInput"
                            type="number"
                            step="0.01"
                            :min="originalBalance"
                            max="999999.99"
                            placeholder="0.00"
                            required
                        />
                        <div v-else class="h-10 flex items-center px-3 text-sm font-medium text-muted-foreground bg-muted/40 rounded-md border border-input/30">
                            New Balance:&nbsp;<span class="text-foreground font-semibold">${{ balance }}</span>
                            <span class="text-xs text-emerald-500 ml-2 font-normal">(+${{ selectedBalanceAdd }})</span>
                        </div>
                        <InputError :message="errors.balance" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="processing">Update Card</Button>
                    <Link href="/cards">
                        <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                </div>
            </Form>
        </div>
    </div>
</template>
