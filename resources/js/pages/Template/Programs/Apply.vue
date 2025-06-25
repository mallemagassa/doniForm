<script setup>
import AppLayout from "@/pages/Template/Layouts/AppLayouts.vue"; // Chemin standard
import { useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
  program: Object,
  fields: Array,
});

// Gestion du dark mode
const isDarkMode = computed(() => 
  document.documentElement.getAttribute("data-theme") === "dark"
);

// Initialisation dynamique du formulaire
const form = useForm(
  props.fields.reduce((acc, field) => {
    acc[field.name] = field.type === "file" ? null : "";
    return acc;
  }, {})
);

// Soumission du formulaire
const submit = () => {
  form.post(route("applications.store", props.program.id), {
    forceFormData: true,
    onSuccess: () => {
      form.reset();
      // Redirection ou notification ici
    },
    onError: (errors) => {
      console.error("Erreurs de validation:", errors);
    }
  });
};
</script>

<template>
  <AppLayout :title="`Postuler - ${program.title}`">
    <section class="py-5 application-section">
      <div class="container" data-aos="fade-up">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="shadow-sm application-card">
              <h1 class="mb-4 application-title">
                Postuler à <strong>{{ program.title }}</strong>
              </h1>
              
              <form @submit.prevent="submit" enctype="multipart/form-data">
                <div v-for="(field, index) in fields" :key="index" class="mb-4">
                  <label :for="field.name" class="form-label fw-semibold">
                    {{ field.label }}
                    <span v-if="field.required" class="text-danger">*</span>
                  </label>

                  <!-- Champ Texte/Email/Number -->
                  <input
                    v-if="['text', 'email', 'number'].includes(field.type)"
                    :type="field.type"
                    class="form-control"
                    :id="field.name"
                    v-model="form[field.name]"
                    :required="field.required"
                  />

                  <!-- Textarea -->
                  <textarea
                    v-else-if="field.type === 'textarea'"
                    class="form-control"
                    :id="field.name"
                    v-model="form[field.name]"
                    :required="field.required"
                    rows="4"
                  ></textarea>

                  <!-- Select -->
                  <select
                    v-else-if="field.type === 'select'"
                    class="form-select"
                    :id="field.name"
                    v-model="form[field.name]"
                    :required="field.required"
                  >
                    <option value="" disabled>Sélectionnez...</option>
                    <option 
                      v-for="(option, optIndex) in field.options" 
                      :key="optIndex" 
                      :value="option.value || option"
                    >
                      {{ option.label || option }}
                    </option>
                  </select>

                  <!-- Date -->
                  <input
                    v-else-if="field.type === 'date'"
                    type="date"
                    class="form-control"
                    :id="field.name"
                    v-model="form[field.name]"
                    :required="field.required"
                  />

                  <!-- Fichier -->
                  <input
                    v-else-if="field.type === 'file'"
                    type="file"
                    class="form-control"
                    :id="field.name"
                    @change="form[field.name] = $event.target.files[0]"
                    :required="field.required"
                    :accept="field.accept || '*'"
                  />

                  <!-- Affichage des erreurs -->
                  <div 
                    v-if="form.errors[field.name]" 
                    class="invalid-feedback d-block"
                  >
                    {{ form.errors[field.name] }}
                  </div>
                </div>

                <div class="mt-4 d-grid">
                  <button 
                    type="submit" 
                    class="btn btn-primary btn-lg"
                    :disabled="form.processing"
                  >
                    <span v-if="form.processing">
                      <span class="spinner-border spinner-border-sm me-2"></span>
                      Envoi en cours...
                    </span>
                    <span v-else>
                      Soumettre ma candidature
                    </span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<style scoped>
.application-section {
  background-color: var(--bs-light-bg-subtle);
  margin-top:90px;
}

.application-card {
  background: white;
  border-radius: 12px;
  padding: 2.5rem;
}

.application-title {
  font-size: 1.8rem;
  border-bottom: 2px solid #2755A1;
  padding-bottom: 0.5rem;
}

.form-label {
  font-weight: 500;
  color: var(--bs-gray-700);
}

.form-control, .form-select {
  border-radius: 8px;
  padding: 0.75rem 1rem;
  border: 1px solid var(--bs-gray-300);
  transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
  border-color: var(--bs-primary);
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn-primary {
  background-color: #95b71d;
  border: none;
  padding: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.btn-primary:hover {
  background-color: #fff;
  color: #95b71d;
  border-color: #95b71d;
  transform: translateY(-2px);
}

.invalid-feedback {
  color: var(--bs-danger);
  font-size: 0.875rem;
  margin-top: 0.25rem;
}
</style>