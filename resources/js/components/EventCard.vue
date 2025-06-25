<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import PenIcon from '@/assets/icons/pen.svg'
import TalmanLogo from '@/assets/images/talman-logo.webp';
import ShowMoreDialog from '@/components/ShowMoreDialog.vue';

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

interface Props {
    event: Event;
    isPast?: boolean;
}

defineProps<Props>();

const openModal = ref(false);


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
    <div class="block bg-white rounded-lg  shadow-sm w-full sm:p-6 sm:pr-4 duration-200 ease-in-out">
        <div class="block relative sm:flex sm:justify-between">
            <div class="flex flex-col gap-6 sm:flex-row">
                <div class="mr-0 sm:mr-8">
              
                    <div v-if="event.image">
                        <img 
                            :src="event.image.url" 
                            width="600" 
                            height="400" 
                            alt="Event Image" 
                            class="w-full h-32 rounded-t-lg object-cover sm:h-36 sm:max-w-64 sm:w-64 sm:rounded-lg" 
                            loading="lazy" 
                        />
                    </div>
                    <div v-else>
                        <img 
                            :src="TalmanLogo" 
                            alt="The Talman Group" 
                            class="w-full h-32 object-contain p-6 bg-gray-100 rounded-t-lg sm:mr-8 sm:h-36 sm:max-w-64 sm:w-64 sm:p-0 sm:rounded-lg sm:bg-none"
                        />
                    </div>
                </div>
                <div class="flex flex-col items-start justify-center gap-1 pb-12 px-6 sm:mr-16 sm:pb-0 sm:p-0">
                    <div class="text-sm font-bold">{{ formatDate(event.date, event.end_date) }}</div>
                    <h1 class="text-lg font-semibold">{{ event.title }}</h1>
                    <div class="text-green-600 text-sm" v-if="event.location">{{ event.location }}</div>
                    <button
                        class="text-green-600 hover:underline text-sm mt-2"
                        @click="openModal = true"
                        type="button"
                    >
                     Show more
                    </button>
                </div>
            </div>

            <!-- Action Button -->
            <div class="absolute bottom-4 right-4 sm:relative sm:flex sm:items-center">
                <Link :href="`/events/${event.uuid}/edit`">
                    <PenIcon class="w-5 h-5" alt="Edit Event"/>
                </Link>
            </div>
        </div>
    </div>
    <ShowMoreDialog
        v-model:open="openModal"
        :event="event"
    />
</template> 