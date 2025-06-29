<script setup lang="ts">
import { 
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogClose
} from '@/components/ui/dialog';


interface Users {
    id: number;
    name: string;
    slug: string;
    avatar: string | null;
 
}

interface ShowMoreDialogProps {
    open: boolean;
    users: User[];
}

const props = defineProps<ShowMoreDialogProps>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const handleOpenChange = (value: boolean) => {
    emit('update:open', value);
};


</script>

<template>
    <Dialog :open="props.open" @update:open="handleOpenChange">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-xl font-semibold">{{ event.title }}</DialogTitle>
                <DialogClose />
            </DialogHeader>
            
            <!-- Event Image -->
            <div class="mb-4">
                
               
            </div>
            
            <div class="space-y-4">
                <div class="text-sm font-bold">
                    {{ formatDate(event.date, event.end_date) }}
                </div>
                
                <div v-if="event.location" class="text-green-600 text-sm">
                    {{ event.location }}
                </div>
                
                <div v-if="event.url" class="space-y-1">
                    <div class="font-semibold text-sm">More Information:</div>
                    <a 
                        v-if="event.url.startsWith('http')" 
                        :href="event.url" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        class="text-blue-600 hover:underline text-sm break-all"
                    >
                        {{ event.url }}
                    </a>
                    <div v-else class="text-sm">{{ event.url }}</div>
                </div>
                
                <div v-if="event.description" class="text-sm">
                    {{ event.description }}
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>