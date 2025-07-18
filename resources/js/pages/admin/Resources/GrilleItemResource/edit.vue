<script setup>
import { ref, onMounted } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import Button from '@/components/ui/button/Button.vue'
import { toast } from 'vue-sonner'
import Select from '@/components/ui/select/Select.vue'
import SelectItem from '@/components/ui/select/SelectItem.vue'
import { SelectTrigger, SelectValue, SelectContent } from '@/components/ui/select'
import { Plus, Minus } from 'lucide-vue-next'

const props = defineProps({
  grilleItem: Object,
  form: Object,
  resource: Object,
})

// Initialiser le formulaire
const form = useForm({
  titre: props.grilleItem.titre,
  base_notation: props.grilleItem.base_notation,
  grille_label_id: props.grilleItem.grille_label_id,
  notes: props.grilleItem.note_items.map(item => ({ 
    id: item.id,
    note: item.note 
  }))
})

// Variable pour suivre si le select est initialisé
const selectInitialized = ref(false)

onMounted(() => {
  // Marquer le select comme initialisé après le rendu
  selectInitialized.value = true
})

function addNote() {
  form.notes.push({ note: '' })
}

function removeNote(index) {
  form.notes.splice(index, 1)
}

function submitForm() {
  form.put(props.resource.routes.update.replace(':id', props.grilleItem.id), {
    onSuccess: () => {
      toast.success(`${props.resource.label} mis à jour avec succès`)
    },
    onError: () => {
      toast.error('Erreur lors de la mise à jour')
    }
  })
}

function getSelectValue(id) {
  // Gestion des booléens
  if (id === 'true') return true
  if (id === 'false') return false
  
  // Gestion des nombres
  if (!isNaN(id)) return Number(id)
  
  // Valeur par défaut
  return id
}
</script>

<template>
  <Head :title="`Modifier ${resource.label}`" />

  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Modifier {{ resource.label }}</h1>
        <Link :href="resource.routes.index">
          <Button variant="default">Retour à la liste</Button>
        </Link>
      </div>

      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Champ titre -->
          <div class="space-y-2">
            <label class="block font-medium capitalize">Titre</label>
            <Input
              v-model="form.titre"
              type="text"
              required
            />
            <span v-if="form.errors.titre" class="text-sm text-red-600">
              {{ form.errors.titre }}
            </span>
          </div>

          <!-- Champ base_notation -->
          <div class="space-y-2">
            <label class="block font-medium capitalize">Base notation</label>
            <Input
              v-model="form.base_notation"
              type="number"
              required
            />
            <span v-if="form.errors.base_notation" class="text-sm text-red-600">
              {{ form.errors.base_notation }}
            </span>
          </div>

          <!-- Champ grille_label_id -->
          <div class="space-y-2">
            <label class="block font-medium capitalize">Grille label</label>
            <Select 
              v-model="form.grille_label_id" 
              required
            >
              <SelectTrigger class="w-full">
                <SelectValue 
                  :placeholder="selectInitialized ? '' : 'Chargement...'"
                />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="(name, id) in props.form.grille_label_id.options.options"
                  :key="id"
                  :value="getSelectValue(id)"
                >
                  {{ name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <span v-if="form.errors.grille_label_id" class="text-sm text-red-600">
              {{ form.errors.grille_label_id }}
            </span>
          </div>

          <!-- Repeater pour les notes -->
          <div class="space-y-4 col-span-full">
            <label class="block font-medium capitalize">Notes</label>
            
            <div v-for="(item, index) in form.notes" :key="index" class="flex items-end gap-2">
              <div class="flex-1 space-y-2">
                <Input
                  v-model="item.note"
                  type="number"
                  required
                  :max="5"
                  placeholder="Note (0-5)"
                />
                <span v-if="form.errors[`notes.${index}.note`]" class="text-sm text-red-600">
                  {{ form.errors[`notes.${index}.note`] }}
                </span>
              </div>
              
              <Button 
                type="button" 
                variant="destructive" 
                size="sm" 
                @click="removeNote(index)"
                :disabled="form.notes.length <= 1"
              >
                <Minus class="w-4 h-4" />
              </Button>
            </div>
            
            <Button 
              type="button" 
              variant="outline" 
              size="sm" 
              @click="addNote"
            >
              <Plus class="w-4 h-4 mr-2" />
              Ajouter une note
            </Button>
          </div>
        </div>

        <div class="flex gap-2">
          <Button type="submit" :disabled="form.processing">Enregistrer</Button>
          <Link :href="resource.routes.index">
            <Button variant="default">Annuler</Button>
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>