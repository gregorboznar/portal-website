<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Calendar } from 'lucide-vue-next';
</script>

<template>
    <Card class="w-full max-w-sm">
        <CardContent class="p-6">
            <div class="flex items-start space-x-4">
                <div class="bg-gray-200 p-2 rounded-lg flex flex-col items-center">
                    <div class="w-4 h-1 bg-gray-400 rounded-full mb-1"></div>
                    <div class="w-4 h-1 bg-gray-400 rounded-full mb-1"></div>
                    <Calendar class="w-8 h-8 text-gray-700" />
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Events</h2>
                    <p class="text-sm text-gray-500">Upcoming Events and Activities</p>
                </div>
                <button class="text-sm text-green-600 hover:underline ml-auto">Show all</button>
            </div>

            <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                <p class="font-semibold text-gray-800">23. July 2025 – 30. July 2025 (8 days)</p>
                <h3 class="text-xl font-bold text-gray-900 mt-1">Munchen event</h3>
                <p class="text-md text-green-600">Munchen</p>
            </div>
        </CardContent>
    </Card>
</template>