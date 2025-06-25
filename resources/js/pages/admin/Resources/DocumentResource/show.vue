<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card'

const props = defineProps({
  document: Object,
  resource: Object,
})

function formatDate(value) {
  if (!value || value === '1970-01-01T00:00:01.000000Z') return 'Non soumis'
  return new Date(value).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getBadgeVariant(status) {
  const variants = {
    draft: 'warning',
    submitted: 'info',
    approved: 'success',
    rejected: 'destructive'
  }
  return variants[status] || 'default'
}
</script>

<template>
  <Head :title="`Détails ${resource.label}`" />

  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Détails {{ resource.label }}</h1>
        <div class="flex gap-2">
          <Link :href="`${resource.routes.edit.replace(':id', document.id)}`">
            <Button variant="outline">Modifier</Button>
          </Link>
          <Link :href="resource.routes.index">
            <Button variant="outline">Retour à la liste</Button>
          </Link>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-4">
            <!-- <span>Document #{{ document.id }}</span>
            <Badge :variant="getBadgeVariant(document.status)">
              {{ document.status }}
            </Badge> -->
          </CardTitle>
        </CardHeader>

        <CardContent class="grid gap-4 md:grid-cols-2">
          <!-- Informations de base -->
          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Label</h3>
            <p class="text-sm whitespace-pre-line">{{ document.label || 'Non renseigné' }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Fichier</h3>
            <p class="text-sm whitespace-pre-line">{{ document.filePath || 'Non renseigné' }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Date de Telechargement</h3>
            <p class="text-sm whitespace-pre-line">{{ formatDate(document.upload_at) || 'Non renseigné' }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Date de création</h3>
            <p class="text-sm">{{ formatDate(document.created_at) }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Date de modification</h3>
            <p class="text-sm">{{ formatDate(document.updated_at) }}</p>
          </div>

          <!-- Relations -->
          <div class="space-y-1" v-if="document.application">
            <h3 class="text-sm font-medium text-gray-500">Application </h3>
            <p class="text-sm">{{ document.application.status }}</p>
            <p class="text-sm text-gray-600">{{ document.application.notes }}</p>
          </div>
          
        </CardContent>

        <CardFooter class="flex justify-between items-center border-t pt-4">
          <div class="text-sm text-gray-500">
            <!-- Statut: 
            <Badge :variant="getBadgeVariant(document.status)" class="ml-2">
              {{ document.status }}
            </Badge> -->
          </div>
        </CardFooter>
      </Card>
    </div>
  </AppLayout>
</template>