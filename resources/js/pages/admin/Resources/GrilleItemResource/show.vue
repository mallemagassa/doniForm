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
            <span>  </span>
            <Badge :variant="getStatusBadgeVariant(application.is_active)">
              
            </Badge>
          </CardTitle>
        </CardHeader>

        <CardContent class="grid gap-4 md:grid-cols-2">
          <!-- Informations principales -->
          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Nom complet</h3>
            <p class="text-sm">  </p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Paye</h3>
            <p class="text-sm"> </p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Description</h3>
            <p class="text-sm whitespace-pre-line"> </p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Statut</h3>
            <p class="text-sm">
              <Badge :variant="getStatusBadgeVariant(application.is_active)">
                
                
              </Badge>
            </p>
          </div>

          <!-- Métadonnées -->
          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Date de création</h3>
            <p class="text-sm">{{ formatDate(application.created_at) }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Dernière modification</h3>
            <p class="text-sm">{{ formatDate(application.updated_at) }}</p>
          </div>


        </CardContent>

        <CardFooter class="flex justify-between items-center border-t pt-4">
          <div class="text-sm text-gray-500">
           
          </div>
        </CardFooter>
      </Card>
    </div>
  </AppLayout>
</template>