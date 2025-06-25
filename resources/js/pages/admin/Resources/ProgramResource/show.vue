<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card'

const props = defineProps({
  program: Object,
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

function getBadgeVariant(status) {
  const variants = {
    draft: 'warning',
    active: 'success',
    archived: 'secondary',
    cancelled: 'destructive'
  }
  return variants[status] || 'default'
}
</script>

<template>
  <Head :title="`Détails ${resource.label}`" />

  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Détails du programme</h1>
        <div class="flex gap-2">
          <Link :href="`${resource.routes.edit.replace(':id', program.id)}`">
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
            <span>{{ program.title }}</span>
            <Badge :variant="getBadgeVariant(program.status)">
              {{ program.status }}
            </Badge>
          </CardTitle>
        </CardHeader>

        <CardContent class="grid gap-4 md:grid-cols-2">
          <!-- Informations principales -->
          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Description</h3>
            <p class="text-sm whitespace-pre-line">{{ program.description || 'Non renseigné' }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Région</h3>
            <p class="text-sm">{{ program.region?.name || 'Non renseigné' }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Date de début</h3>
            <p class="text-sm">{{ formatDate(program.start_date) }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Date de fin</h3>
            <p class="text-sm">{{ formatDate(program.end_date) }}</p>
          </div>

          <!-- Responsable et créateur -->
          <!-- <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Responsable</h3>
            <p class="text-sm">{{ program.user?.name || 'Non renseigné' }}</p>
          </div> -->

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Créé par</h3>
            <p class="text-sm">{{ program.user?.name || 'Non renseigné' }}</p>
          </div>

          <!-- Métadonnées -->
          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Date de création</h3>
            <p class="text-sm">{{ formatDate(program.created_at) }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Dernière modification</h3>
            <p class="text-sm">{{ formatDate(program.updated_at) }}</p>
          </div>
        </CardContent>

        <CardFooter class="flex justify-between items-center border-t pt-4">
          <div class="text-sm text-gray-500">
            ID: {{ program.id }}
          </div>
          <Badge :variant="getBadgeVariant(program.status)">
            Statut: {{ program.status }}
          </Badge>
        </CardFooter>
      </Card>
    </div>
  </AppLayout>
</template>