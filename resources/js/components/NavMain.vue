<script setup lang="ts">
import { SidebarGroup, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import RightIcon from '@/assets/icons/right.svg'

defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();

const isActiveRoute = (itemHref: string): boolean => {
    const currentUrl = page.url;
    
    // Exact match for root/dashboard
    if (itemHref === '/dashboard') {
        return currentUrl === '/dashboard';
    }
    
    // For other routes, check if current URL starts with the item href
    return currentUrl.startsWith(itemHref);
};
</script>

<template>
    <SidebarGroup class="px-4 py-0">
        <!-- <SidebarGroupLabel>Platform</SidebarGroupLabel> -->
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton 
                    as-child :is-active="isActiveRoute(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href" class="flex items-center justify-between group/link">
                        <div class="flex items-center justify-between">
                            <component :is="item.icon" />
                            <span class="ml-14" :class="{ 'font-semibold': isActiveRoute(item.href) }">{{ item.title }}</span>
                        </div>
                            <RightIcon v-if="isActiveRoute(item.href)" class="transition-transform duration-300 group-hover/link:translate-x-1" />
                       
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
