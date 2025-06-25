<script setup>
import AppLayout from "@/pages/Template/Layouts/AppLayouts.vue"; // Chemin standard
import { Link, router, Head } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
  programs: Object, // Doit être un objet pagination, non un Array
});

const showProgram = (id) => {
  router.get(route("program.show", id));
};
</script>

<template>
  <AppLayout title="Programmes - DoniForm">
    <section class="py-5 programs-section">
      <div class="container">
        <div class="mb-5 text-center">
          <h2 class="section-title">
            Nos Programmes
          </h2>
          <p class="lead text-muted">
            Incubation, mentorat et accompagnement sur mesure.
          </p>
        </div>

        <!-- Liste des programmes -->
        <div class="row gy-4">
          <div 
            v-for="program in programs.data"  
            :key="program.id" 
            class="col-md-4"
          >
            <div class="border-0 shadow-sm program-card card h-100">
              <div class="card-body">
                <div class="mb-3 text-accent">
                  <i class="bi bi-lightbulb fs-1"></i>
                </div>
                <h3 class="h5 card-title fw-bold">{{ program.title }}</h3>
                <p class="card-text text-muted">
                  {{ program.description.substring(0, 100) }}...
                </p>
                <div class="mt-3 d-flex justify-content-between">
                  <button
                    class="btn btn-outline-primary btn-sm"
                    @click="showProgram(program.id)"
                  >
                    Voir détails
                  </button>
                  <Link
                    :href="route('apply', program.id)"
                    class="btn btn-primary btn-sm"
                  >
                    Postuler
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination - Placée en DEHORS de la boucle v-for -->
        <div class="mt-5 d-flex justify-content-center">
          <nav aria-label="Navigation des programmes">
            <ul class="pagination">
              <li 
                v-for="link in programs.links" 
                :key="link.label"
                class="page-item"
                :class="{ 
                  'active': link.active,
                  'disabled': !link.url
                }"
              >
                <Link
                  v-if="link.url"
                  :href="link.url"
                  class="page-link"
                  v-html="link.label"
                  preserve-scroll
                />
                <span v-else class="page-link" v-html="link.label"></span>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<style scoped>
.programs-section{
  margin-top: 90px;
}

.program-card {
  transition: all 0.3s ease;
  border-radius: 10px;
}

.program-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.section-title {
  font-size: 2rem;
  position: relative;
  display: inline-block;
}

.section-title:after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 80px;
  height: 3px;
  background: var(--primary);
}

.page-item.active .page-link {
  background-color: var(--primary);
  border-color: var(--primary);
}

.page-link {
  color: var(--primary);
}

.btn.btn-primary {
  color: #fff;
  background-color: #95b71d;
  border-color: #95b71d;
}

/* Optionnel : styles pour les états hover/focus */
.btn.btn-primary:hover,
.btn.btn-primary:focus {
  background-color: #85a71a;
  border-color: #85a71a;
}
.btn.btn-outline-primary {
  color: #95b71d;
  background-color: #fff;
  border-color: #95b71d;
}

/* Optionnel : styles pour les états hover/focus */
.btn.btn-outline-primary:hover,
.btn.btn-outline-primary:focus {
  background-color: #85a71a;
  border-color: #85a71a;
  color: #fff;
}
</style>