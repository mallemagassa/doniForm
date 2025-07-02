<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

const props = defineProps({
  evaluationcriteria: Object,
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
</script>

<template>
  <Head :title="`Détails ${resource.label}`" />

  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Détails du critère d'évaluation</h1>
        <div class="flex gap-2">
          <Link :href="`${resource.routes.edit.replace(':id', evaluationcriteria.id)}`">
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
            <span>Critère #{{ evaluationcriteria.id }}</span>
          </CardTitle>
        </CardHeader>

        <CardContent class="grid gap-4 md:grid-cols-2">
          <!-- Informations principales -->
          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Programme</h3>
            <p class="text-sm">{{ evaluationcriteria.program?.title || 'Non renseigné' }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Label</h3>
            <p class="text-sm">{{ evaluationcriteria.label || 'Non renseigné' }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Description</h3>
            <p class="text-sm whitespace-pre-line">{{ evaluationcriteria.description || 'Non renseigné' }}</p>
          </div>

          <!-- Métadonnées -->
          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Date de création</h3>
            <p class="text-sm">{{ formatDate(evaluationcriteria.created_at) }}</p>
          </div>

          <div class="space-y-1">
            <h3 class="text-sm font-medium text-gray-500">Dernière modification</h3>
            <p class="text-sm">{{ formatDate(evaluationcriteria.updated_at) }}</p>
          </div>
        </CardContent>

        <!-- Section pour les items du repeater -->
        <CardContent class="border-t pt-4">
          <h3 class="text-lg font-semibold mb-4">Éléments d'évaluation</h3>
          
          <Table v-if="evaluationcriteria.evaluation_criteria_items?.length">
            <TableHeader>
              <TableRow>
                <TableHead class="font-bold">Titre</TableHead>
                <TableHead class="font-bold">Description</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in evaluationcriteria.evaluation_criteria_items" :key="item.id">
                <TableCell class="font-medium font-bold">{{ item.title }}</TableCell>
                <TableCell class="whitespace-pre-line">{{ item.description }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <div v-else class="text-center py-4 text-gray-500">
            Aucun élément d'évaluation défini
          </div>
        </CardContent>

        <CardFooter class="flex justify-between items-center border-t pt-4">
          <div class="text-sm text-gray-500">
            ID: {{ evaluationcriteria.id }}
          </div>
        </CardFooter>
      </Card>
    </div>
  </AppLayout>
</template>