<script setup>
import AppLayout from "@/pages/Template/Layouts/AppLayouts.vue";
import { useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
  program: Object,
  fields: Array, // Tableau des champs dynamiques
  formConfig: Object, // Configuration supplémentaire du formulaire
});

// Initialisation dynamique du formulaire avec valeurs par défaut
const form = useForm(
  props.fields.reduce((acc, field) => {
    acc[field.name] = field.default_value || 
                     (field.type === 'file' ? null : 
                     (field.type === 'checkbox' ? false : ''));
    return acc;
  }, {})
);

// Soumission du formulaire
const submit = () => {
  const method = props.formConfig?.method || 'post';
  const url = props.formConfig?.action || route("applications.store", props.program.id);

  form.transform(data => {
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => {
      if (value !== null && value !== undefined) {
        formData.append(key, value);
      }
    });
    return formData;
  })[method](url, {
    forceFormData: true,
    onSuccess: () => {
      form.reset();
      // Gérer la redirection ou notification ici
    },
    onError: (errors) => {
      console.error("Validation errors:", errors);
    }
  });
};

// Gestion des types de champs complexes
const fieldComponents = {
  text: 'input',
  email: 'input',
  number: 'input',
  password: 'input',
  tel: 'input',
  url: 'input',
  date: 'input',
  datetime: 'input',
  time: 'input',
  textarea: 'textarea',
  select: 'select',
  checkbox: 'checkbox',
  radio: 'radio',
  file: 'file',
  multiselect: 'multiselect',
  toggle: 'toggle',
  range: 'range'
};
</script>

<template>
  <AppLayout :title="`Postuler - ${program.title}`">
    <section class="py-5 application-section">
      <div class="container" data-aos="fade-up">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="shadow-sm application-card">
              <!-- En-tête du formulaire -->
              <div class="mb-4 text-center">
                <h1 class="application-title">
                  Postuler à <strong>{{ program.title }}</strong>
                </h1>
                <p class="text-muted" v-if="program.description">
                  {{ program.description.substring(0, 120) }}...
                </p>
              </div>
              
              <!-- Formulaire dynamique -->
              <form @submit.prevent="submit" enctype="multipart/form-data">
                <div class="mb-4" v-for="(field, index) in fields" :key="index">
                  <!-- Label du champ -->
                  <label :for="field.name" class="form-label fw-semibold">
                    {{ field.label }}
                    <span v-if="field.required" class="text-danger">*</span>
                    <small class="text-muted ms-2" v-if="field.help_text">
                      {{ field.help_text }}
                    </small>
                  </label>

                  <!-- Champ Texte/Email/Number/Date etc. -->
                  <input
                    v-if="['text', 'email', 'number', 'password', 'tel', 'url', 'date', 'datetime-local', 'time'].includes(field.type)"
                    :type="field.type"
                    class="form-control"
                    :id="field.name"
                    v-model="form[field.name]"
                    :required="field.required"
                    :placeholder="field.placeholder || ''"
                    :min="field.min"
                    :max="field.max"
                    :step="field.step"
                    :pattern="field.pattern"
                  />

                  <!-- Textarea -->
                  <textarea
                    v-else-if="field.type === 'textarea'"
                    class="form-control"
                    :id="field.name"
                    v-model="form[field.name]"
                    :required="field.required"
                    :rows="field.rows || 4"
                    :placeholder="field.placeholder || ''"
                  ></textarea>

                  <!-- Select -->
                  <select
                    v-else-if="field.type === 'select'"
                    class="form-select"
                    :id="field.name"
                    v-model="form[field.name]"
                    :required="field.required"
                    :multiple="field.multiple || false"
                  >
                    <option value="" disabled v-if="!field.multiple">
                      {{ field.placeholder || 'Sélectionnez...' }}
                    </option>
                    <option 
                      v-for="(option, optIndex) in field.options" 
                      :key="optIndex" 
                      :value="option.value || option"
                    >
                      {{ option.label || option }}
                    </option>
                  </select>

                  <!-- Checkbox -->
                  <div v-else-if="field.type === 'checkbox'" class="form-check">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :id="field.name"
                      v-model="form[field.name]"
                      :required="field.required"
                    />
                    <label class="form-check-label" :for="field.name">
                      {{ field.checkbox_label || field.label }}
                    </label>
                  </div>

                  <!-- Radio Buttons -->
                  <div v-else-if="field.type === 'radio'" class="radio-group">
                    <div 
                      v-for="(option, optIndex) in field.options" 
                      :key="optIndex" 
                      class="form-check"
                    >
                      <input
                        class="form-check-input"
                        type="radio"
                        :id="`${field.name}-${optIndex}`"
                        v-model="form[field.name]"
                        :value="option.value || option"
                        :required="field.required && optIndex === 0"
                      />
                      <label 
                        class="form-check-label" 
                        :for="`${field.name}-${optIndex}`"
                      >
                        {{ option.label || option }}
                      </label>
                    </div>
                  </div>

                  <!-- Fichier -->
                  <div v-else-if="field.type === 'file'">
                    <input
                      type="file"
                      class="form-control"
                      :id="field.name"
                      @change="form[field.name] = $event.target.files[0]"
                      :required="field.required"
                      :accept="field.accept || '*'"
                      :multiple="field.multiple || false"
                    />
                    <small class="text-muted" v-if="field.file_types">
                      Formats acceptés: {{ field.file_types }}
                    </small>
                  </div>

                  <!-- Affichage des erreurs -->
                  <div 
                    v-if="form.errors[field.name]" 
                    class="invalid-feedback d-block"
                  >
                    <i class="bi bi-exclamation-circle me-1"></i>
                    {{ form.errors[field.name] }}
                  </div>
                </div>

                <!-- Conditions générales -->
                <div class="mb-4 form-check" v-if="formConfig?.terms_url">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="terms_accepted"
                    v-model="form.terms_accepted"
                    required
                  />
                  <label class="form-check-label" for="terms_accepted">
                    J'accepte les <a :href="formConfig.terms_url" target="_blank">conditions générales</a>
                  </label>
                </div>

                <!-- Bouton de soumission -->
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
                      <i class="bi bi-send me-2"></i>
                      {{ formConfig?.submit_text || 'Soumettre ma candidature' }}
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
  min-height: 100vh;
  margin-top: 90px;
}

.application-card {
  background: var(--bs-body-bg);
  border-radius: 12px;
  padding: 2.5rem;
  border: 1px solid var(--bs-border-color);
}

.application-title {
  font-size: 1.8rem;
  border-bottom: 2px solid var(--bs-primary);
  padding-bottom: 0.75rem;
  display: inline-block;
}

.form-label {
  font-weight: 500;
  color: var(--bs-gray-700);
  margin-bottom: 0.5rem;
}

.form-control, .form-select, .form-check-input {
  border-radius: 8px;
  padding: 0.75rem 1rem;
  border: 1px solid var(--bs-gray-300);
  transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
  border-color: var(--bs-primary);
  box-shadow: 0 0 0 0.25rem rgba(149, 183, 29, 0.25);
}

.form-check-input:checked {
  background-color: #95b71d;
  border-color: #95b71d;
}

.radio-group, .checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.btn-primary {
  background-color: #95b71d;
  border: none;
  padding: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background-color: #7a9a16;
  transform: translateY(-2px);
}

.btn-primary:disabled {
  background-color: #95b71d;
  opacity: 0.7;
}

.invalid-feedback {
  color: var(--bs-danger);
  font-size: 0.875rem;
  margin-top: 0.25rem;
  display: flex;
  align-items: center;
}

/* Style pour les fichiers téléchargés */
.file-preview {
  margin-top: 0.5rem;
  font-size: 0.9rem;
}

/* Dark mode support */
[data-theme="dark"] .application-card {
  background: var(--bs-dark-bg-subtle);
  border-color: var(--bs-dark-border-color);
}

[data-theme="dark"] .form-control, 
[data-theme="dark"] .form-select {
  background-color: var(--bs-dark-bg);
  border-color: var(--bs-dark-border-color);
  color: var(--bs-dark-text);
}
</style>