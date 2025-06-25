<script setup lang="ts">
import { cn } from '@/lib/utils';
import * as icons from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    name: string;
    size?: number | string;
    class?: string;
    color?: string;
    strokeWidth?: number | string;
}

const props = withDefaults(defineProps<Props>(), {
    size: 16,
    class: '',
    strokeWidth: 4,
});

const className = computed(() => cn('h-4 w-4', props.class));

const icon = computed(() => {
    const iconName = props.name.charAt(0).toUpperCase() + props.name.slice(1);
    return (icons as Record<string, any>)[iconName];
});
</script>

<template>
    <component :is="icon" :class="className" :size="size" :stroke-width="strokeWidth" :color="color" />
</template>
