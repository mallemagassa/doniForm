<script setup>
import AppLayout from "@/pages/Template/Layouts/AppLayouts.vue"; // Chemin standard
import { Link, router, Head } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
  program: Object
});

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR');
};
</script>

<template>
  <AppLayout title="Détails du programme" :programs="[]">
    <section class="py-5 program-details">
      <div class="container">
        <div class="row g-5">
          <!-- Colonne principale -->
          <div class="col-lg-8">
            <div class="program-content">
              <h1 class="mb-4 program-title">{{ program.title }}</h1>
              
              <div class="mb-5 program-description">
                <h3 class="mb-3 h5">Description</h3>
                <p class="lead">{{ program.description }}</p>
              </div>
              
              <!-- Section supplémentaire si disponible -->
              <div v-if="program.additional_info" class="mt-5 additional-info">
                <h3 class="mb-3 h5">Informations complémentaires</h3>
                <div v-html="program.additional_info"></div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="col-lg-4">
            <div class="border-0 shadow-sm program-sidebar card">
              <div class="p-4 card-body">
                <h2 class="mb-4 h4 card-title">Détails du programme</h2>
                
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Date de début :</span>
                    <span>{{ formatDate(program.start_date) }}</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Date de fin :</span>
                    <span>{{ formatDate(program.end_date) }}</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Statut :</span>
                    <span class="badge bg-success">{{ program.status }}</span>
                  </li>
                </ul>

                <Link 
                  :href="route('apply', program.id)" 
                  class="py-2 mt-4 btn btn-primary w-100"
                >
                  <i class="bi bi-send-fill me-2"></i>
                  Postuler maintenant
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<style scoped>
.program-details {
  background-color: #f8f9fa;
  margin-top: 90px;
}

.program-title {
  color: #2c3e50;
  font-weight: 700;
  border-bottom: 2px solid #95b71d;
  padding-bottom: 0.5rem;
}

.program-sidebar {
  position: sticky;
  top: 20px;
}

.btn-primary {
  background-color: #95b71d;
  border-color: #95b71d;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background-color: #7e9a1a;
  border-color: #7e9a1a;
  transform: translateY(-2px);
}

.list-group-item {
  padding: 1rem 0;
  border-color: rgba(0,0,0,0.05);
}
</style>