<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card'

const props = defineProps({
  region: Object,
  resource: Object,
})

function formatDate(value) {
  if (!value) return 'Non défini'
  return new Date(value).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function getStatusBadgeVariant(isActive) {
  return isActive ? 'success' : 'destructive'
}
</script>

<template>
  <Head :title="`Détails ${resource.label}`" />

  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Détails de la région</h1>
        <div class="flex gap-2">
          <Link :href="`${resource.routes.edit.replace(':id', region.id)}`">
            <Button variant="default">Modifier</Button>
          </Link>
          <Link :href="resource.routes.index">
            <Button variant="default">Retour à la liste</Button>
          </Link>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-4">
            <span>{{ region.name }}</span>
            <Badge :variant="getStatusBadgeVariant(region.is_active)">
              {{ region.is_active ? 'Active' : 'Inactive' }}
            </Badge>
          </CardTitle>
        </CardHeader>

        <CardContent class="grid gap-4 md:grid-cols-2">
          <!-- Informations principales -->
          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Nom complet</h3>
            <p class="text-sm">{{ region.name || 'Non renseigné' }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Paye</h3>
            <p class="text-sm">{{ region.country || 'Non renseigné' }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Description</h3>
            <p class="text-sm whitespace-pre-line">{{ region.description || 'Non renseigné' }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Statut</h3>
            <p class="text-sm">
              <Badge :variant="getStatusBadgeVariant(region.is_active)">
                {{ region.is_active ? 'Active' : 'Inactive' }}
              </Badge>
            </p>
          </div>

          <!-- Métadonnées -->
          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Date de création</h3>
            <p class="text-sm">{{ formatDate(region.created_at) }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Dernière modification</h3>
            <p class="text-sm">{{ formatDate(region.updated_at) }}</p>
          </div>

          <!-- Responsable -->
          <div class="space-y-1" v-if="region.manager">
            <h3 class="text-sm font-medium text-gray-500">Responsable</h3>
            <p class="text-sm">{{ region.manager.name }}</p>
            <p class="text-sm text-gray-600">{{ region.manager.email }}</p>
          </div>
        </CardContent>

        <CardFooter class="flex justify-between items-center border-t pt-4">
          <div class="text-sm text-gray-500">
            Region: {{ region.name }}
          </div>
        </CardFooter>
      </Card>
    </div>
  </AppLayout>
</template>