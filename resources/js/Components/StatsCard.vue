<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: String,
    value: [String, Number],
    icon: Object,
    trend: Number,
    trendText: String,
    color: {
        type: String,
        default: 'indigo'
    }
});

const colorClasses = computed(() => {
    const colors = {
        indigo: 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400',
        emerald: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        amber: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
        rose: 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
        blue: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
    };
    return colors[props.color] || colors.indigo;
});
</script>

<template>
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-300">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ title }}</p>
                <h3 class="text-3xl font-bold mt-2 text-slate-800 dark:text-white">{{ value }}</h3>
                
                <div v-if="trend !== undefined" class="mt-4 flex items-center space-x-2">
                    <span 
                        :class="[
                            'text-xs font-bold px-2 py-1 rounded-full',
                            trend >= 0 ? 'bg-emerald-500/10 text-emerald-600' : 'bg-rose-500/10 text-rose-600'
                        ]"
                    >
                        {{ trend >= 0 ? '+' : '' }}{{ trend }}%
                    </span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ trendText }}</span>
                </div>
            </div>
            
            <div :class="['p-4 rounded-2xl', colorClasses]">
                <component :is="icon" class="w-6 h-6" />
            </div>
        </div>
    </div>
</template>
