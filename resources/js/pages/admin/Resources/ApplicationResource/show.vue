<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card'

const props = defineProps({
  application: Object,
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
          <Link :href="`${resource.routes.edit.replace(':id', application.id)}`">
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
            <span>Demande #{{ application.id }}</span>
            <Badge :variant="getBadgeVariant(application.status)">
              {{ application.status }}
            </Badge>
          </CardTitle>
        </CardHeader>

        <CardContent class="grid gap-4 md:grid-cols-2">
          <!-- Informations de base -->
          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Données du formulaire</h3>
            <p class="text-sm whitespace-pre-line">{{ application.form_data || 'Non renseigné' }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Notes</h3>
            <p class="text-sm whitespace-pre-line">{{ application.notes || 'Non renseigné' }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Date de soumission</h3>
            <p class="text-sm">{{ formatDate(application.submitted_at) }}</p>
          </div>

          <!-- Relations -->
          <div class="space-y-1" v-if="application.user">
            <h3 class="text-sm font-medium text-gray-500">Utilisateur</h3>
            <p class="text-sm">{{ application.user.name }}</p>
            <p class="text-sm text-gray-600">{{ application.user.email }}</p>
          </div>

          <div class="space-y-1" v-if="application.program">
            <h3 class="text-sm font-medium text-gray-500">Programme</h3>
            <p class="text-sm">{{ application.program.title }}</p>
            <p class="text-sm text-gray-600">{{ application.program.description }}</p>
          </div>
        </CardContent>

        <CardFooter class="flex justify-between items-center border-t pt-4">
          <div class="text-sm text-gray-500">
            Créé le {{ formatDate(application.created_at) }}
            <span v-if="application.updated_at !== application.created_at">
              · Modifié le {{ formatDate(application.updated_at) }}
            </span>
          </div>
        </CardFooter>
      </Card>
    </div>
  </AppLayout>
</template>