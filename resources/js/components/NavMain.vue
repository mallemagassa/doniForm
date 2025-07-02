<script setup lang="ts">
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubItem,
} from '@/components/ui/sidebar'

import { type NavItem } from '@/types'
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { ChevronDown, ChevronRight } from 'lucide-vue-next' // ✅ icônes

defineProps<{
  items: NavItem[]
}>()

const page = usePage()
const openGroup = ref<string | null>(null)

const toggleGroup = (group: string) => {
  openGroup.value = openGroup.value === group ? null : group
}
</script>

<template>
  <SidebarGroup class="px-2 py-0">
    <SidebarGroupLabel> Plateforme </SidebarGroupLabel>
    <SidebarMenu>
      <template v-for="item in items" :key="item.title">
        <!-- Dropdown -->
        <SidebarMenuItem v-if="item.children">
          <SidebarMenuButton as-child @click="toggleGroup(item.title)">
            <div class="flex items-center justify-between w-full gap-2 cursor-pointer">
              <div class="flex items-center gap-2">
                <!-- <component :is="item.icon" /> -->
                <span>{{ item.title }}</span>
              </div>
              <component
                :is="openGroup === item.title ? ChevronDown : ChevronRight"
                class="w-4 h-4 text-muted-foreground transition-transform duration-200"
              />
            </div>
          </SidebarMenuButton>

          <SidebarMenuSub v-if="openGroup === item.title">
            <SidebarMenuSubItem
              v-for="child in item.children"
              :key="child.title"
            >
              <SidebarMenuButton as-child :is-active="child.href === page.url">
                <Link :href="child.href" class="flex items-center gap-2">
                  <component :is="child.icon" />
                  <span>{{ child.title }}</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuSubItem>
          </SidebarMenuSub>
        </SidebarMenuItem>

        <!-- Simple -->
        <SidebarMenuItem v-else>
          <SidebarMenuButton
            as-child
            :is-active="item.href === page.url"
          >
            <Link :href="item.href" class="flex items-center gap-2">
              <component :is="item.icon" />
              <span>{{ item.title }}</span>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </template>
    </SidebarMenu>
  </SidebarGroup>
</template>
