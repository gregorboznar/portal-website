<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Heart, MessageCircle, Eye, MoreHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';
import axios from 'axios';


</script>

<template>
   <div class="w-72 flex-shrink-0 hidden xl:block">
      <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold mb-4">Upcoming Events</h3>
        <p class="text-gray-500 text-sm">No upcoming events</p>
      </div>
    </div>
</template> 