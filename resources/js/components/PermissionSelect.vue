<script setup>
import { computed } from 'vue'
import { Switch } from '@/components/ui/switch'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  options: {
    type: Object,
    required: true
  },
  multiple: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const selectedValues = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const isSelected = (permissionId) => {
  return selectedValues.value.includes(permissionId)
}

const togglePermission = (permissionId) => {
  if (isSelected(permissionId)) {
    selectedValues.value = selectedValues.value.filter(id => id !== permissionId)
  } else {
    selectedValues.value = [...selectedValues.value, permissionId]
  }
}

// Grouper les permissions par ressource
const groupedPermissions = computed(() => {
  const groups = {}
  const permissionsArray = props.options[""] || []
  
  permissionsArray.forEach(permission => {
    const resourceMatch = permission.name.match(/(\w+)_resource$/)
    if (resourceMatch) {
      const resourceName = resourceMatch[1]
      if (!groups[resourceName]) {
        groups[resourceName] = []
      }
      groups[resourceName].push(permission)
    } else {
      if (!groups['Autres']) {
        groups['Autres'] = []
      }
      groups['Autres'].push(permission)
    }
  })
  
  return groups
})

// Toutes les permissions
const allPermissions = computed(() => {
  return props.options[""] || []
})

// Sélectionner/désélectionner toutes les permissions
const toggleAllPermissions = () => {
  if (selectedValues.value.length === allPermissions.value.length) {
    selectedValues.value = []
  } else {
    selectedValues.value = allPermissions.value.map(p => p.id)
  }
}

// Vérifier si toutes les permissions sont sélectionnées
const allSelected = computed(() => {
  return selectedValues.value.length === allPermissions.value.length && allPermissions.value.length > 0
})

// Vérifier si toutes les permissions d'un groupe sont sélectionnées
const allGroupSelected = (permissions) => {
  return permissions.length > 0 && permissions.every(p => selectedValues.value.includes(p.id))
}

// Sélectionner/désélectionner tout un groupe
const toggleGroup = (permissions) => {
  const groupIds = permissions.map(p => p.id)
  if (allGroupSelected(permissions)) {
    selectedValues.value = selectedValues.value.filter(id => !groupIds.includes(id))
  } else {
    // Ajouter seulement les permissions manquantes
    const newSelected = [...selectedValues.value]
    groupIds.forEach(id => {
      if (!newSelected.includes(id)) {
        newSelected.push(id)
      }
    })
    selectedValues.value = newSelected
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Carte pour le contrôle global -->
    <Card>
      <CardHeader class="flex flex-row items-center justify-between pb-2">
        <CardTitle class="text-sm font-medium">Permissions globales</CardTitle>
        <div class="flex items-center gap-2">
          <Switch
            :checked="allSelected"
            @click="toggleAllPermissions"
          />
          <span class="text-sm">
            {{ allSelected ? 'Tout désélectionner' : 'Tout sélectionner' }}
          </span>
        </div>
      </CardHeader>
    </Card>

    <!-- Cartes pour chaque groupe de permissions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <Card v-for="(permissions, resourceName) in groupedPermissions" :key="resourceName">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <CardTitle class="text-sm font-medium capitalize">
            {{ resourceName.replace(/_/g, ' ') }}
          </CardTitle>
          <div class="flex items-center gap-2">
            <Switch
              :checked="allGroupSelected(permissions)"
              @click="toggleGroup(permissions)"
            />
            <span class="text-xs">
              {{ allGroupSelected(permissions) ? 'Tout' : 'Rien' }}
            </span>
          </div>
        </CardHeader>
        
        <CardContent class="pt-2">
          <div class="space-y-3">
            <div v-for="permission in permissions" :key="permission.id" class="flex items-center space-x-2">
              <input
                type="checkbox"
                :id="`permission-${permission.id}`"
                :checked="isSelected(permission.id)"
                @change="togglePermission(permission.id)"
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              >
              <label :for="`permission-${permission.id}`" class="text-sm text-gray-700 capitalize">
                {{ permission.name.split('_')[0] }}
              </label>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

