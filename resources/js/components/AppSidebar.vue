<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const { props } = usePage()


const panel = computed(() => props.panel ?? 'admin')
const resources = computed(() => props.resources ?? [])

// console.log('Panel:', resources.value)

const mainNavItems = computed<NavItem[]>(() => [
  {
    title: 'Dashboard',
    href: `/${panel.value}`,
    icon: LayoutGrid,
  },
  ...resources.value.map((resource: { label: string; routeName: string }) => ({
    title: resource.label,
    href: `/${panel.value}/${resource.routeName}`,
    icon: Folder, // ou un mapping si tu veux une icône différente par ressource
  })),
])

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route(`${panel}.dashboard`)">
                            <!-- <AppLogo /> -->
                            DONI FORM
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
