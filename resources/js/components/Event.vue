<script setup lang="ts">
import EventIcon from '@/assets/icons/events.svg'
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Heart, MessageCircle, Eye, MoreHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';
import axios from 'axios';


</script>

<template>
    <div class="flex justify-between items-center mb-4 pl-3">
  <div class="flex gap-2 items-start w-full">
    <EventIcon class="w-5 h-5 relative top-1" />
    <div class="flex flex-col items-start w-full">
      <div class="flex justify-between items-center w-full">
        <h1 >Events</h1>
        <a href="<?= url('events') ?>" class="text-green hover:text-green/70 text-sm underline transition-colors duration-300 mr-4">Show all</a>
      </div>
      <p class="p-s">Upcoming Events and Activities</p>
    </div>
  </div>
</div>
   <div class="w-72 flex-shrink-0 hidden xl:block">
      <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold mb-4">Upcoming Events</h3>
        <p class="text-gray-500 text-sm">No upcoming events</p>
      </div>
    </div>
</template> 