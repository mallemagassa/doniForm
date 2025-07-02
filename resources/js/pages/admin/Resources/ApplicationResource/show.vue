<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card'
import {
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow
} from '@/components/ui/table'
import { computed } from 'vue'
import {
  DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger
} from '@/components/ui/dropdown-menu'

const props = defineProps({
  application: Object,
  resource: Object,
  evaluationcriteria: {
    type: Object,
    default: () => ({})
  },
  statusOptions: {
    type: Array,
    default: () => [
      { value: 'pending', label: 'En attente' },
      { value: 'approved', label: 'Approuvé' },
      { value: 'rejected', label: 'Rejeté' }
    ]
  }
})

function formatDate(value) {
  if (!value || value === '1970-01-01T00:00:01.000000Z') return 'Non soumis'
  return new Date(value).toLocaleDateString('fr-FR', {
    year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

function getBadgeVariant(status) {
  const variants = {
    draft: 'warning', submitted: 'info',
    approved: 'success', rejected: 'destructive'
  }
  return variants[status] || 'default'
}

const formFields = computed(() => {
  if (!props.application.form_data) return []

  try {
    const data = typeof props.application.form_data === 'string'
      ? JSON.parse(props.application.form_data)
      : props.application.form_data

    return Object.entries(data).map(([key, field]) => ({
      id: key,
      label: field.label || 'Sans libellé',
      value: field.value || 'Non renseigné',
      type: field.type,
      options: field.options
    }))
  } catch (e) {
    console.error('Erreur de parsing des données du formulaire', e)
    return []
  }
})

function updateStatus(newStatus) {
  router.put(props.resource.routes.update.replace(':id', props.application.id), {
    status: newStatus
  })
}


function toggleCheck(item) {
  router.put(`/evaluation-items/${item.id}/toggle`, {
    is_checked: !item.is_checked,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      item.is_checked = !item.is_checked
    },
    onError: () => {
      console.error('Erreur lors de la mise à jour')
    }
  })
}


</script>

<template>
  <Head :title="`Détails ${resource.label}`" />

  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Détails {{ resource.label }}</h1>
        <div class="flex gap-2">
          <Link :href="resource.routes.index">
            <Button variant="default">Retour à la liste</Button>
          </Link>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-4">
            <span>Programme #{{ application.program.title }}</span>
            <Badge :variant="getBadgeVariant(application.status)">
              {{ application.status }}
            </Badge>
          </CardTitle>
        </CardHeader>

        <CardContent class="grid gap-6">
          <!-- Métadonnées -->
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-1">
              <h3 class="text-sm font-medium text-gray-500">Date de soumission</h3>
              <p class="text-sm">{{ formatDate(application.submitted_at) }}</p>
            </div>

            <!-- Relations -->
            <div class="space-y-1" v-if="application.user">
              <h3 class="text-sm font-medium text-gray-500">Candidat</h3>
              <p class="text-sm">{{ application.user.name }}</p>
              <p class="text-sm text-gray-600">{{ application.user.email }}</p>
            </div>

            <div class="space-y-1" v-if="application.program">
              <h3 class="text-sm font-medium text-gray-500">Programme</h3>
              <p class="text-sm">{{ application.program.title }}</p>
              <p class="text-sm text-gray-600">{{ application.program.description }}</p>
            </div>
          </div>

          <!-- Tableau des réponses avec action de changement de statut -->
          <div class="border rounded-lg overflow-hidden">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="w-[200px]">Libellé / Question</TableHead>
                  <TableHead>Réponse du candidat</TableHead>
                  <TableHead class="w-[150px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <!-- Ligne pour le statut -->
                <TableRow>
                  <TableCell class="font-medium">
                    Statut de la candidature
                  </TableCell>
                  <TableCell>
                    <Badge :variant="getBadgeVariant(application.status)">
                      {{ application.status }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm" class="h-8">
                          Modifier
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent>
                        <DropdownMenuItem 
                          v-for="option in statusOptions" 
                          :key="option.value"
                          @click="updateStatus(option.value)"
                          :class="{
                            'bg-gray-100': application.status === option.value,
                            'text-destructive': option.value === 'rejected',
                            'text-success': option.value === 'approved'
                          }"
                        >
                          {{ option.label }}
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </TableCell>
                </TableRow>

                <!-- Autres champs du formulaire -->
                <TableRow v-for="field in formFields" :key="field.id">
                  <TableCell class="font-medium">
                    {{ field.label }}
                    <span class="block text-xs text-gray-500 mt-1">
                      {{ field.type }}
                    </span>
                  </TableCell>
                  <TableCell>
                    <template v-if="field.type === 'file' && field.value.path">
                      <a :href="`/storage/${field.value.path}`" 
                         target="_blank"
                         class="text-primary hover:underline">
                        {{ field.value.original_name }}
                      </a>
                    </template>
                    <template v-else-if="Array.isArray(field.value)">
                      {{ field.value.join(', ') }}
                    </template>
                    <template v-else>
                      {{ field.value }}
                    </template>
                  </TableCell>
                  <TableCell>
                    <!-- Espace réservé pour d'autres actions si nécessaire -->
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>

        <CardContent class="border-t pt-4">
          <h3 class="text-lg font-semibold mb-4">Éléments d'évaluation</h3>
          
          <Table v-if="props.evaluationcriteria?.evaluation_criteria_items?.length">
            <TableHeader>
              <TableRow>
                <TableHead class="font-bold">Titre</TableHead>
                <TableHead class="font-bold">Description</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="item in props.evaluationcriteria.evaluation_criteria_items"
                :key="item.id"
              >
                <TableCell class="font-medium font-bold">{{ item.title }}</TableCell>
                <TableCell class="whitespace-pre-line">{{ item.description }}</TableCell>
                <TableCell class="text-center">
                  <input
                    type="checkbox"
                    :checked="item.is_checked"
                    @change="toggleCheck(item)"
                    class="h-5 w-5 accent-primary"
                  />
                </TableCell>
              </TableRow>
            </TableBody>


          </Table>

          <div v-else class="text-center py-4 text-gray-500">
            Aucun élément d'évaluation défini
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