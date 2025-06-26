<script setup lang="ts">
import EventIcon from '@/assets/icons/events.svg'
import { Link } from '@inertiajs/vue3';

interface Event {
    id: number;
    title: string;
    short_description?: string;
    description?: string;
    date: string;
    end_date?: string;
    location?: string;
    status: string;
    image?: {
        id: number;
        url: string;
    };
}

interface Props {
    events: Event[];
}

defineProps<Props>();

const formatDate = (date: string, endDate?: string) => {
    const startDate = new Date(date);
    const options: Intl.DateTimeFormatOptions = { 
        month: 'short', 
        day: 'numeric'
    };
    
    if (endDate && endDate !== date) {
        const end = new Date(endDate);
        return `${startDate.toLocaleDateString('en-US', options)} - ${end.toLocaleDateString('en-US', options)}`;
    }
    
    return startDate.toLocaleDateString('en-US', options);
};
</script>

<template>
    <div class="flex justify-between items-center mb-4 pl-3">
        <div class="flex gap-2 items-start w-full">
            <EventIcon class="w-6 h-6 relative top-1" />
            <div class="flex flex-col items-start w-full">
                <div class="flex justify-between items-center w-full">
                    <h1>Events</h1>
                    <Link href="/events" class="text-green hover:text-green/70 text-sm underline transition-colors duration-300 mr-4">
                        Show all
                    </Link>
                </div>
                <p class="p-s">Upcoming Events and Activities</p>
            </div>
        </div>
    </div>
    
    <div class="w-72 flex-shrink-0 hidden xl:block">
        <div class="bg-white rounded-lg shadow p-4">
            <div v-if="events.length === 0" class="text-gray-500 text-sm">
                No upcoming events
            </div>
            <div v-else class="space-y-4">
                <div v-for="event in events" :key="event.id" class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                  <Link :href="`/events`">
                    <div class="flex gap-3">
                        <div v-if="event.image" class="flex-shrink-0">
                            <img 
                                :src="event.image.url" 
                                :alt="event.title"
                                class="w-12 h-12 rounded object-cover"
                            />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-sm text-gray-900 line-clamp-2">
                                {{ event.title }}
                            </h4>
                            <p class="min-text">
                                {{ formatDate(event.date, event.end_date) }}
                            </p>
                            <p v-if="event.location" class="green-text">
                                {{ event.location }}
                            </p>
                        </div>
                    </div>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template> 