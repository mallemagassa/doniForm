<script setup>
import AppLayout from "@/pages/Template/Layouts/AppLayouts.vue";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
  program: Object,
  critere: Array,
});
</script>

<template>
  <AppLayout :title="`Critères d'évaluation - ${program.title}`">
    <section class="py-5 application-section">
      <div class="container" data-aos="fade-up">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="shadow-sm application-card">
              <!-- En-tête -->
              <div class="mb-4 text-center">
                <h1 class="application-title">
                  Critères d'évaluation pour <strong>{{ program.title }}</strong>
                </h1>
                <p class="text-muted" v-if="program.description">
                  {{ program.description.substring(0, 120) }}...
                </p>
              </div>

              <!-- Liste des critères -->
              <div v-if="critere.length > 0" class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Titre</th>
                      <th scope="col">Description</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in critere" :key="item.id">
                      <th scope="row">{{ index + 1 }}</th>
                      <td>{{ item.title }}</td>
                      <td class="text-truncate" style="max-width: 200px;">
                        {{ item.description }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Message si aucun critère -->
              <div v-else class="text-center py-5">
                <div class="mb-3">
                  <i class="bi bi-info-circle display-5 text-muted"></i>
                </div>
                <h5 class="text-muted">Aucun critère d'évaluation défini</h5>
              </div>
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

.table {
  --bs-table-bg: transparent;
  --bs-table-striped-bg: rgba(149, 183, 29, 0.05);
}

.table-hover tbody tr:hover {
  --bs-table-accent-bg: rgba(149, 183, 29, 0.1);
}

.table-light {
  --bs-table-bg: #f8f9fa;
  --bs-table-striped-bg: #ecedee;
  --bs-table-striped-color: #000;
  --bs-table-active-bg: #dfe0e1;
  --bs-table-active-color: #000;
  --bs-table-hover-bg: #e5e6e7;
  --bs-table-hover-color: #000;
}

.btn-primary {
  background-color: #95b71d;
  border: none;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background-color: #7a9a16;
  transform: translateY(-2px);
}

.btn-outline-primary {
  color: #95b71d;
  border-color: #95b71d;
}

.btn-outline-primary:hover {
  background-color: #95b71d;
  color: white;
}

.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Dark mode support */
[data-theme="dark"] .application-card {
  background: var(--bs-dark-bg-subtle);
  border-color: var(--bs-dark-border-color);
}

[data-theme="dark"] .table-light {
  --bs-table-bg: #343a40;
  --bs-table-striped-bg: #3e444a;
  --bs-table-striped-color: #fff;
  --bs-table-active-bg: #484e53;
  --bs-table-active-color: #fff;
  --bs-table-hover-bg: #42484d;
  --bs-table-hover-color: #fff;
  color: #fff;
}
</style>