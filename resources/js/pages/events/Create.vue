<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import EventIcon from '@/assets/icons/events.svg'
import LeftArrowIcon from '@/assets/icons/left-arrow.svg'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Events',
        href: '/events',
    },
    {
        title: 'Create Event',
        href: '/events/create',
    },
];

interface Props {
    event: Event;
}
const props = defineProps<Props>();

const form = useForm({
    title: '',
    description: '',
    date: '',
    time: '',
    location: '',
});

const submit = () => {
    form.post('/events');
};
</script>

<template>
    <Head title="Create Event" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-lg">
            <div class="flex justify-between">
                <div class="pl-3">
                    <div class="flex items-center gap-3">
                        <EventIcon class="w-5 h-5" />
                        <h1>Create new event</h1>
                    </div>
                    <Link 
                        :href="route('events')" 
                        class="flex items-center gap-2 text-green hover:text-green/70 transition-colors duration-300 text-sm underline ml-2 cursor-pointer"
                    >
                        <LeftArrowIcon class="w-4 h-4" />
                        Back to events
                    </Link>
                </div>
                <div class="flex gap-4 pt-4">
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Creating...' : 'Create Event' }}
                    </Button>
                </div>
            </div>
                <div class="flex gap-6">
                    <Card class="flex-1">
                        <CardContent>
                            <form @submit.prevent="submit" class="space-y-6">
                                <div class="space-y-2">
                                    <Label for="title">Title</Label>
                                    <Input
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        placeholder="Enter event title"
                                        required
                                        :class="{ 'border-red-500': form.errors.title }"
                                    />
                                    <div v-if="form.errors.title" class="text-sm text-red-500">
                                        {{ form.errors.title }}
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        placeholder="Enter event description"
                                        rows="4"
                                        :class="{ 'border-red-500': form.errors.description }"
                                    />
                                    <div v-if="form.errors.description" class="text-sm text-red-500">
                                        {{ form.errors.description }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="date">Start Date</Label>
                                        <Input
                                            id="date"
                                            v-model="form.date"
                                            type="date"
                                            required
                                            :class="{ 'border-red-500': form.errors.date }"
                                        />
                                        <div v-if="form.errors.date" class="text-sm text-red-500">
                                            {{ form.errors.date }}
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="time">Start date</Label>
                                        <Input
                                            id="time"
                                            v-model="form.time"
                                            type="time"
                                            required
                                            :class="{ 'border-red-500': form.errors.time }"
                                        />
                                        <div v-if="form.errors.time" class="text-sm text-red-500">
                                            {{ form.errors.time }}
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="location">Location</Label>
                                    <Input
                                        id="location"
                                        v-model="form.location"
                                        type="text"
                                        placeholder="Enter event location"
                                        :class="{ 'border-red-500': form.errors.location }"
                                    />
                                    <div v-if="form.errors.location" class="text-sm text-red-500">
                                        {{ form.errors.location }}
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <Button type="submit" :disabled="form.processing">
                                        {{ form.processing ? 'Creating...' : 'Create Event' }}
                                    </Button>
                                    <Button type="button" variant="outline" @click="router.visit('/events')">
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                    
                    <Card class="flex-1">
                        <CardContent>
                            <form @submit.prevent="submit" class="space-y-6">

                                <div class="space-y-2">
                                    <Label for="title">Event Title *</Label>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
        </div>
    </AppLayout>
</template>