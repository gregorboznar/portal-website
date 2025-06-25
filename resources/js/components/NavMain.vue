<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import RightIcon from '@/assets/icons/right.svg'

defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();
</script>

<template>
    <SidebarGroup class="px-4 py-0">
        <!-- <SidebarGroupLabel>Platform</SidebarGroupLabel> -->
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton 
                    as-child :is-active="item.href === page.url"
                    :tooltip="item.title"
                >
                    <Link :href="item.href" class="flex items-center justify-between">
                        <div class="flex items-center justify-between">
                            <component :is="item.icon" />
                            <span class="ml-14">{{ item.title }}</span>
                        </div>
                            <RightIcon v-if="item.href == page.url" class="" />
                       
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
