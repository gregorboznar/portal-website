<script setup lang="ts">
import { 
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogClose
} from '@/components/ui/dialog';
import TalmanLogo from '@/assets/images/talman-logo.webp';

interface Event {
    id: number;
    uuid: string;
    title: string;
    description?: string;
    short_description?: string;
    date: string;
    time?: string;
    end_date?: string;
    location?: string;
    url?: string;
    registration_url?: string;
    status?: string;
    image?: {
        id: number;
        url: string;
        path?: string;
    };
}

interface ShowMoreDialogProps {
    open: boolean;
    event: Event;
}

const props = defineProps<ShowMoreDialogProps>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const handleOpenChange = (value: boolean) => {
    emit('update:open', value);
};

const formatDate = (dateString: string, endDateString?: string) => {
    const start = new Date(dateString);
    
    if (endDateString && endDateString !== dateString) {
        const end = new Date(endDateString);
        const timeDiff = end.getTime() - start.getTime();
        const numDays = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
        
        return `${start.toLocaleDateString('en-US', {
            day: 'numeric',
            month: 'long', 
            year: 'numeric'
        })} – ${end.toLocaleDateString('en-US', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        })} (${numDays} days)`;
    }
    
    return start.toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
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
                <div v-if="event.image">
                    <img 
                        :src="event.image.url" 
                        alt="Event Image" 
                        class="w-full h-48 rounded-lg object-cover" 
                        loading="lazy" 
                    />
                </div>
                <div v-else>
                    <img 
                        :src="TalmanLogo" 
                        alt="The Talman Group" 
                        class="w-full h-48 object-contain p-6 bg-gray-100 rounded-lg"
                    />
                </div>
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